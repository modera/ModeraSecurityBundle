<?php

namespace Modera\SecurityBundle\Tests\Functional\DataInstallation;

use Modera\SecurityBundle\DataInstallation\BCLayer;
use Modera\SecurityBundle\Entity\Permission;
use Modera\SecurityBundle\Entity\PermissionCategory;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class BCLayerTest extends AbstractTestCase
{
    /**
     * @var BCLayer
     */
    private $bcLayer;

    public function doSetUp()
    {
        $this->bcLayer = self::$container->get('modera_security.data_installation.bc_layer');
    }

    public function testSyncPermissionCategoryTechnicalNamesInDatabase_onlyOldCategoryExists()
    {
        $oldCategory = new PermissionCategory('User mgm', 'user-management');

        self::$em->persist($oldCategory);
        self::$em->flush();
        self::$em->clear();

        $this->bcLayer->syncPermissionCategoryTechnicalNamesInDatabase();

        // Old category should have just been renamed to a new name

        /* @var PermissionCategory $oldCategory */
        $newCategory = self::$em->find(PermissionCategory::clazz(), $oldCategory->getId());
        $this->assertInstanceOf(PermissionCategory::clazz(), $newCategory);
        $this->assertEquals('administration', $newCategory->getTechnicalName());
    }

    public function testSyncPermissionCategoryTechnicalNamesInDatabase_bothOldAndNewCategoriesExist()
    {
        $newCategory = new PermissionCategory('Administration', 'administration');
        $oldCategory = new PermissionCategory('User mgm', 'user-management');

        $perm = new Permission();
        $perm->setName('Foo perm');
        $perm->setRoleName('ROLE_FOO');
        $perm->setCategory($newCategory);

        self::$em->persist($newCategory);
        self::$em->persist($oldCategory);
        self::$em->persist($perm);
        self::$em->flush();
        self::$em->clear();

        $this->bcLayer->syncPermissionCategoryTechnicalNamesInDatabase();

        self::$em->clear(); // because otherwise $newCategory still is in UoW

        /* @var PermissionCategory $oldCategory */
        $renamedOldCategory = self::$em->find(PermissionCategory::clazz(), $oldCategory->getId());
        $this->assertInstanceOf(PermissionCategory::clazz(), $renamedOldCategory);
        $this->assertEquals('administration', $renamedOldCategory->getTechnicalName());

        /* @var Permission $perm */
        $perm = self::$em->find(Permission::clazz(), $perm->getId());
        $this->assertEquals($perm->getCategory()->getId(), $oldCategory->getId());

        $deletedNewCategory = self::$em->find(PermissionCategory::clazz(), $newCategory->getId());
        $this->assertTrue(null === $deletedNewCategory);
    }
}