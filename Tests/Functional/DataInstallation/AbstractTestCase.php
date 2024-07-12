<?php

namespace Modera\SecurityBundle\Tests\Functional\DataInstallation;

use Doctrine\ORM\Tools\SchemaTool;
use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Modera\SecurityBundle\Entity\Group;
use Modera\SecurityBundle\Entity\Permission;
use Modera\SecurityBundle\Entity\PermissionCategory as PermissionCategoryEntity;
use Modera\SecurityBundle\Entity\Permission as PermissionEntity;
use Modera\SecurityBundle\Entity\User;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class AbstractTestCase extends FunctionalTestCase
{
    /**
     * @var SchemaTool
     */
    private static $st;

    public static function doSetUpBeforeClass(): void
    {
        self::$st = new SchemaTool(self::$em);
        self::$st->createSchema([
            self::$em->getClassMetadata(User::class),
            self::$em->getClassMetadata(Group::class),
            self::$em->getClassMetadata(Permission::class),
            self::$em->getClassMetadata(PermissionEntity::class),
            self::$em->getClassMetadata(PermissionCategoryEntity::class),
        ]);
    }

    public static function doTearDownAfterClass(): void
    {
        self::$st->dropSchema([
            self::$em->getClassMetadata(User::class),
            self::$em->getClassMetadata(Group::class),
            self::$em->getClassMetadata(Permission::class),
            self::$em->getClassMetadata(PermissionEntity::class),
            self::$em->getClassMetadata(PermissionCategoryEntity::class),
        ]);
    }
}
