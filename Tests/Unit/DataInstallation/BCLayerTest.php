<?php

namespace Modera\SecurityBundle\Tests\Unit\DataInstallation;

use Modera\SecurityBundle\DataInstallation\BCLayer;

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
        $this->bcLayer = new BCLayer();
    }

    public function testResolveNewPermissionCategoryTechnicalName_match()
    {
        $this->assertEquals('administration', $this->bcLayer->resolveNewPermissionCategoryTechnicalName('user-management'));
    }

    public function testResolveNewPermissionCategoryTechnicalName_noMatch()
    {
        $this->assertFalse($this->bcLayer->resolveNewPermissionCategoryTechnicalName('foobar'));
    }
}