<?php

namespace Modera\SecurityBundle\Tests\Functional\DataInstallation;

use Doctrine\ORM\Tools\SchemaTool;
use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Modera\SecurityBundle\Entity\PermissionCategory as PermissionCategoryEntity;
use Modera\SecurityBundle\Entity\Permission as PermissionEntity;

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

    /**
     * {@inheritdoc}
     */
    public static function doSetUpBeforeClass()
    {
        self::$st = new SchemaTool(self::$em);
        self::$st->createSchema(array(self::$em->getClassMetadata(PermissionEntity::class)));
        self::$st->createSchema(array(self::$em->getClassMetadata(PermissionCategoryEntity::class)));
    }

    /**
     * {@inheritdoc}
     */
    public static function doTearDownAfterClass()
    {
        self::$st->dropSchema(array(self::$em->getClassMetadata(PermissionEntity::class)));
        self::$st->dropSchema(array(self::$em->getClassMetadata(PermissionCategoryEntity::class)));
    }
}