<?php

namespace Modera\SecurityBundle\Tests\Unit\DataInstallation;

use Modera\SecurityBundle\DataInstallation\BCLayer;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class BCLayerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BCLayer
     */
    private $bcLayer;

    public function setUp()
    {
        $this->bcLayer = new BCLayer(\Phake::mock(ManagerRegistry::class));
    }

    /**
     * @dataProvider mappingProvider
     */
    public function testResolveNewPermissionCategoryTechnicalName_match($old, $new)
    {
        $this->assertEquals($new, $this->bcLayer->resolveNewPermissionCategoryTechnicalName($old));
    }

    public function mappingProvider()
    {
        return [
            ['user-management', 'administration'],
            ['site', 'general'],
        ];
    }

    public function testResolveNewPermissionCategoryTechnicalName_noMatch()
    {
        $this->assertFalse($this->bcLayer->resolveNewPermissionCategoryTechnicalName('foobar'));
    }
}