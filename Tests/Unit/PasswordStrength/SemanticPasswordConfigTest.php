<?php

namespace Modera\SecurityBundle\Tests\Unit\PasswordStrength;

use Modera\SecurityBundle\PasswordStrength\SemanticPasswordConfig;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class SemanticPasswordConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SemanticPasswordConfig
     */
    private $config;

    public function setUp(): void
    {
        $this->config = new SemanticPasswordConfig(array(
            'password_strength' => array(
                'enabled' => true,
                'number_required' => true,
                'letter_required' => 'test',
                'rotation_period' => 123,
            ),
        ));
    }

    public function testIsEnabled()
    {
        $this->assertTrue($this->config->isEnabled());
    }

    public function testNumberRequired()
    {
        $this->assertTrue($this->config->isNumberRequired());
    }

    public function testLetterRequired()
    {
        $this->assertTrue($this->config->isLetterRequired());
        $this->assertEquals('test', $this->config->getLetterRequiredType());
    }

    public function testRotationPeriod()
    {
        $this->assertEquals(123, $this->config->getRotationPeriodInDays());
    }
}
