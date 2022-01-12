<?php

namespace Modera\SecurityBundle\Tests\Unit\DataInstallation;

use Modera\SecurityBundle\DataInstallation\BCLayer;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class BCLayerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BCLayer
     */
    private $bcLayer;

    public function setUp(): void
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