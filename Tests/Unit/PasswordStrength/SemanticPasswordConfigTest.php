<?php

namespace Modera\SecurityBundle\Tests\Unit\PasswordStrength;

use Modera\SecurityBundle\PasswordStrength\SemanticPasswordConfig;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class SemanticPasswordConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SemanticPasswordConfig
     */
    private $config;

    public function setUp()
    {
        $this->config = new SemanticPasswordConfig(array(
            'password_strength' => array(
                'enabled' => 'foo1',
                'number_required' => 'foo2',
                'letter_required' => 'foo3',
                'rotation_period' => 'foo4',
            ),
        ));
    }

    public function testIsEnabled()
    {
        $this->assertEquals('foo1', $this->config->isEnabled());
    }

    public function testNumberRequired()
    {
        $this->assertEquals('foo2', $this->config->isNumberRequired());
    }

    public function testLetterRequired()
    {
        $this->assertTrue($this->config->isLetterRequired());
        $this->assertEquals('foo3', $this->config->getLetterRequiredType());
    }

    public function testRotationPeriod()
    {
        $this->assertEquals('foo4', $this->config->getRotationPeriodInDays());
    }
}