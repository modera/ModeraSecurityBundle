<?php

namespace Modera\SecurityBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Modera\SecurityBundle\DependencyInjection\Configuration;
use Modera\SecurityBundle\PasswordStrength\PasswordConfigInterface;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class ConfigurationTest extends \PHPUnit\Framework\TestCase
{
    public function testPasswordStrength()
    {
        $configuration = new Configuration();

        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, array());

        $this->assertArrayHasKey('password_strength', $config);

        $config = $config['password_strength'];
        $expectedConfig = array(
            'mail' => array(
                'service' => 'modera_security.password_strength.mail.default_mail_service',
            ),
            'enabled' => false,
            'min_length' => 6,
            'number_required' => false,
            'letter_required' => false,
            'rotation_period' => 90,
        );
        $this->assertSame($expectedConfig, $config);
    }

    public function testPasswordStrengthLetterRequired()
    {
        $values = array_merge(
            array(true, false),
            PasswordConfigInterface::LETTER_REQUIRED_TYPES,
            array('on')
        );

        foreach ($values as $value) {
            $this->assertPasswordStrengthLetterRequired($value);
        }
    }

    private function assertPasswordStrengthLetterRequired($value)
    {
        $configuration = new Configuration();

        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, array(
            'modera_security' => array(
                'password_strength' => array(
                    'letter_required' => $value,
                ),
            ),
        ));

        $expected = false;
        if (is_bool($value) && $value) {
            $expected = PasswordConfigInterface::LETTER_REQUIRED_TYPE_CAPITAL_OR_NON_CAPITAL;
        } elseif (is_string($value)) {
            $expected = $value;
            if (!in_array($value, PasswordConfigInterface::LETTER_REQUIRED_TYPES)) {
                $expected = PasswordConfigInterface::LETTER_REQUIRED_TYPE_CAPITAL_OR_NON_CAPITAL;
            }
        }

        $expectedConfig = array(
            'mail' => array(
                'service' => 'modera_security.password_strength.mail.default_mail_service',
            ),
            'enabled' => false,
            'min_length' => 6,
            'number_required' => false,
            'letter_required' => $expected,
            'rotation_period' => 90,
        );
        $this->assertEquals($expectedConfig, $config['password_strength']);
    }
}
