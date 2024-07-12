<?php

namespace Modera\SecurityBundle\Tests\Functional\PasswordStrength;

use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Modera\SecurityBundle\PasswordStrength\PasswordConfigInterface;
use Modera\SecurityBundle\PasswordStrength\StrongPassword;
use Modera\SecurityBundle\PasswordStrength\StrongPasswordValidator;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class StrongPasswordValidatorTest extends FunctionalTestCase
{
    public function testValidate()
    {
        $context = $this->createContext();
        $validator = new StrongPasswordValidator($this->createMockPasswordConfig(array()));
        $validator->initialize($context);

        $validator->validate('foobar', new StrongPassword());
        $this->assertEquals(0, count($context->getViolations()));

        // ---

        $context = $this->createContext();
        $validator = new StrongPasswordValidator($this->createMockPasswordConfig(array(
            'enabled' => true,
            'min_length' => 6,
        )));
        $validator->initialize($context);

        $validator->validate('foo', new StrongPassword());
        $this->assertEquals(1, count($context->getViolations()));

        // -

        $context = $this->createContext();
        $validator->initialize($context);

        $validator->validate('foobar1', new StrongPassword());
        $this->assertEquals(0, count($context->getViolations()));

        // ---

        $context = $this->createContext();
        $validator = new StrongPasswordValidator($this->createMockPasswordConfig(array(
            'enabled' => true,
            'number_required' => true,
        )));
        $validator->initialize($context);

        $validator->validate('foobarfoo', new StrongPassword());
        $this->assertEquals(1, count($context->getViolations()));
        $this->assertEquals(
            'Password must contain at least one number character.',
            $context->getViolations()[0]->getMessage()
        );

        // -

        $context = $this->createContext();
        $validator->initialize($context);

        $validator->validate('foobar1', new StrongPassword());
        $this->assertEquals(0, count($context->getViolations()));

        // ---

        $context = $this->createContext();
        $validator = new StrongPasswordValidator($this->createMockPasswordConfig(array(
            'enabled' => true,
            'letter_required' => PasswordConfigInterface::LETTER_REQUIRED_TYPE_CAPITAL,
        )));
        $validator->initialize($context);

        $validator->validate('foobarfoo', new StrongPassword());
        $this->assertEquals(1, count($context->getViolations()));
        $this->assertEquals(
            'Password must contain at least one capital letter.',
            $context->getViolations()[0]->getMessage()
        );

        // -

        $context = $this->createContext();
        $validator->initialize($context);

        $validator->validate('foobAr1', new StrongPassword());
        $this->assertEquals(0, count($context->getViolations()));

        // ---

        $context = $this->createContext();
        $validator = new StrongPasswordValidator($this->createMockPasswordConfig(array(
            'enabled' => true,
            'letter_required' => PasswordConfigInterface::LETTER_REQUIRED_TYPE_NON_CAPITAL,
        )));
        $validator->initialize($context);

        $validator->validate('FOOBARFOO', new StrongPassword());
        $this->assertEquals(1, count($context->getViolations()));
        $this->assertEquals(
            'Password must contain at least one non-capital letter.',
            $context->getViolations()[0]->getMessage()
        );

        // -

        $context = $this->createContext();
        $validator->initialize($context);

        $validator->validate('FOOBaR1', new StrongPassword());
        $this->assertEquals(0, count($context->getViolations()));

        // ---

        $context = $this->createContext();
        $validator = new StrongPasswordValidator($this->createMockPasswordConfig(array(
            'enabled' => true,
            'letter_required' => PasswordConfigInterface::LETTER_REQUIRED_TYPE_CAPITAL_AND_NON_CAPITAL,
        )));
        $validator->initialize($context);

        $validator->validate('123456', new StrongPassword());
        $this->assertEquals(1, count($context->getViolations()));
        $this->assertEquals(
            'Password must contain at least one capital and one non-capital letter.',
            $context->getViolations()[0]->getMessage()
        );

        // -

        $context = $this->createContext();
        $validator->initialize($context);

        $validator->validate('1234aB', new StrongPassword());
        $this->assertEquals(0, count($context->getViolations()));

        // ---

        $context = $this->createContext();
        $validator = new StrongPasswordValidator($this->createMockPasswordConfig(array(
            'enabled' => true,
            'letter_required' => PasswordConfigInterface::LETTER_REQUIRED_TYPE_CAPITAL_OR_NON_CAPITAL,
        )));
        $validator->initialize($context);

        $validator->validate('123456', new StrongPassword());
        $this->assertEquals(1, count($context->getViolations()));
        $this->assertEquals(
            'Password must contain at least one letter.',
            $context->getViolations()[0]->getMessage()
        );

        // -

        $context = $this->createContext();
        $validator->initialize($context);

        $validator->validate('12345a', new StrongPassword());
        $this->assertEquals(0, count($context->getViolations()));

        // -

        $context = $this->createContext();
        $validator->initialize($context);

        $validator->validate('12345A', new StrongPassword());
        $this->assertEquals(0, count($context->getViolations()));

        // -

        $context = $this->createContext();
        $validator->initialize($context);

        $validator->validate('1234Ab', new StrongPassword());
        $this->assertEquals(0, count($context->getViolations()));

        // ---

        $context = $this->createContext();
        $validator = new StrongPasswordValidator($this->createMockPasswordConfig(array(
            'enabled' => false,
            'min_length' => 6,
            'number_required' => true,
            'letter_required' => PasswordConfigInterface::LETTER_REQUIRED_TYPE_CAPITAL_OR_NON_CAPITAL,
        )));
        $validator->initialize($context);

        $validator->validate('foob', new StrongPassword());
        $this->assertEquals(0, count($context->getViolations()));

        // -

        $context = $this->createContext();
        $validator = new StrongPasswordValidator($this->createMockPasswordConfig(array(
            'enabled' => true,
            'min_length' => 6,
            'number_required' => true,
            'letter_required' => PasswordConfigInterface::LETTER_REQUIRED_TYPE_CAPITAL_OR_NON_CAPITAL,
        )));
        $validator->initialize($context);

        $validator->validate(':(', new StrongPassword());
        $this->assertEquals(3, count($context->getViolations()));
    }

    public function createMockPasswordConfig(array $rawConfig)
    {
        $config = \Phake::mock(PasswordConfigInterface::class);

        $mapping = array(
            'number_required' => 'isNumberRequired',
            'enabled' => 'isEnabled',
            'letter_required' => array('isLetterRequired', 'getLetterRequiredType'),
            'min_length' => 'getMinLength',
        );

        foreach ($mapping as $keyName => $methods) {
            if (isset($rawConfig[$keyName])) {
                if (!is_array($methods)) {
                    $methods = array($methods);
                }
                foreach ($methods as $methodName) {
                    \Phake::when($config)
                        ->{$methodName}()
                        ->thenReturn($rawConfig[$keyName])
                    ;
                }
            }
        }

        return $config;
    }

    /**
     * @return ExecutionContext
     */
    private function createContext()
    {
        return new ExecutionContext(
            self::getContainer()->get('validator'),
            '',
            self::getContainer()->get('translator')
        );
    }
}
