<?php

namespace Modera\SecurityBundle\Tests\Functional\DataInstallation;

use Doctrine\ORM\Tools\SchemaTool;
use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Modera\SecurityBundle\DataInstallation\BCLayer;
use Modera\SecurityBundle\DataInstallation\PermissionAndCategoriesInstaller;
use Modera\SecurityBundle\Model\Permission;
use Modera\SecurityBundle\Model\PermissionCategory;
use Sli\ExpanderBundle\Ext\ContributorInterface;
use Modera\SecurityBundle\Entity\PermissionCategory as PermissionCategoryEntity;
use Modera\SecurityBundle\Entity\Permission as PermissionEntity;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
class PermissionAndCategoriesInstallerTest extends FunctionalTestCase
{
    /**
     * @var SchemaTool
     */
    private static $st;

    /**
     * @var PermissionAndCategoriesInstaller
     */
    private $installer;

    private $permissionCategoriesProvider;
    private $permissionsProvider;

    /**
     * {@inheritdoc}
     */
    public static function doSetUpBeforeClass()
    {
        self::$st = new SchemaTool(self::$em);
        self::$st->createSchema(array(self::$em->getClassMetadata(PermissionEntity::clazz())));
        self::$st->createSchema(array(self::$em->getClassMetadata(PermissionCategoryEntity::clazz())));
    }

    /**
     * {@inheritdoc}
     */
    public static function doTearDownAfterClass()
    {
        self::$st->dropSchema(array(self::$em->getClassMetadata(PermissionEntity::clazz())));
        self::$st->dropSchema(array(self::$em->getClassMetadata(PermissionCategoryEntity::clazz())));
    }

    /**
     * {@inheritdoc}
     */
    public function doSetUp()
    {
        $this->permissionCategoriesProvider = $this->createMock(ContributorInterface::CLAZZ);
        $this->permissionsProvider = $this->createMock(ContributorInterface::CLAZZ);

        $this->installer = new PermissionAndCategoriesInstaller(
            self::$em,
            $this->permissionCategoriesProvider,
            $this->permissionsProvider,
            new BCLayer()
        );
    }

    private function getLastRecordInDatabase($entityClass)
    {
        $query = self::$em->createQuery(sprintf('SELECT e FROM %s e ORDER BY e.id DESC', $entityClass));
        $query->setMaxResults(1);

        return $query->getSingleResult();
    }

    private function assertValidResultStructure(array $result)
    {
        $this->assertTrue(is_array($result));
        $this->assertArrayHasKey('installed', $result);
        $this->assertArrayHasKey('removed', $result);
    }

    public function testInstallCategories()
    {
        $category = new PermissionCategory('foo category', 'foo_category');

        $pcp = $this->permissionCategoriesProvider;
        $pcp->expects($this->atLeastOnce())
            ->method('getItems')
            ->will($this->returnValue(array($category)));

        $result = $this->installer->installCategories();

        $this->assertValidResultStructure($result);
        $this->assertEquals(1, $result['installed']);
        $this->assertEquals(0, $result['removed']);

        /* @var PermissionCategoryEntity $installedCategory */
        $installedCategory = $this->getLastRecordInDatabase(PermissionCategoryEntity::clazz());

        $this->assertNotNull($installedCategory);
        $this->assertEquals($category->getName(), $installedCategory->getName());
        $this->assertEquals($category->getTechnicalName(), $installedCategory->getTechnicalName());

        // ---

        $result = $this->installer->installCategories();

        $this->assertValidResultStructure($result);
        $this->assertEquals(0, $result['installed']);
        $this->assertEquals(0, $result['removed']);
    }

    public function testInstallPermission()
    {
        $category = new PermissionCategoryEntity();
        $category->setName('Foo category');
        $category->setTechnicalName('foo_category');

        self::$em->persist($category);
        self::$em->flush();

        $permission = new Permission('foo name', 'FOO_ROLE', $category->getTechnicalName(), 'foo description');

        $pp = $this->permissionsProvider;
        $pp->expects($this->atLeastOnce())
           ->method('getItems')
           ->will($this->returnValue(array($permission)));

        $result = $this->installer->installPermissions();

        $this->assertValidResultStructure($result);
        $this->assertEquals(1, $result['installed']);
        $this->assertEquals(0, $result['removed']);

        /* @var PermissionEntity $installedPermission */
        $installedPermission = $this->getLastRecordInDatabase(PermissionEntity::clazz());

        $this->assertNotNull($installedPermission);
        $this->assertEquals($permission->getName(), $installedPermission->getName());
        $this->assertEquals($permission->getDescription(), $installedPermission->getDescription());
        $this->assertEquals($permission->getRole(), $installedPermission->getRole());
        $this->assertNotNull($installedPermission->getCategory());
        $this->assertEquals($category->getId(), $installedPermission->getCategory()->getId());

        // ---

        $result = $this->installer->installPermissions();

        $this->assertValidResultStructure($result);
        $this->assertEquals(0, $result['installed']);
        $this->assertEquals(0, $result['removed']);
    }

    /**
     * @group MPFE-964
     */
    public function testInstallPermissionWithBCLayer()
    {
        // emulating BCLayer class from ModeraBackendSecurityBundle
        $category1 = new PermissionCategoryEntity();
        $category1->setName('Administration');
        $category1->setTechnicalName('user-management');

        $category2 = new PermissionCategoryEntity();
        $category2->setName('Administration');
        $category2->setTechnicalName('administration');

        self::$em->persist($category1);
        self::$em->persist($category2);
        self::$em->flush();

        // refer to "old" technical name
        $permission = new Permission('foo name', 'FOO_ROLE', $category1->getTechnicalName(), 'foo description');

        $pp = $this->permissionsProvider;
        $pp->expects($this->atLeastOnce())
            ->method('getItems')
            ->will($this->returnValue(array($permission)));

        $result = $this->installer->installPermissions();

        self::$em->clear();

        /* @var PermissionEntity $permission */
        $permission = self::$em->getRepository(PermissionEntity::class)->findOneBy(array('roleName' => 'FOO_ROLE'));

        $this->assertNotNull($permission);
        $this->assertNotNull($permission->getCategory());
        $this->assertEquals('administration', $permission->getCategory()->getTechnicalName());
    }
}
