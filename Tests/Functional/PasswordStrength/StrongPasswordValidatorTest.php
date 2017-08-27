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
            'capital_letter_required' => true,
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
            'enabled' => false,
            'min_length' => 6,
            'number_required' => true,
            'capital_letter_required' => true,
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
            'capital_letter_required' => true,
        )));
        $validator->initialize($context);

        $validator->validate('foob', new StrongPassword());
        $this->assertEquals(3, count($context->getViolations()));
    }

    public function createMockPasswordConfig(array $rawConfig)
    {
        $config = \Phake::mock(PasswordConfigInterface::class);

        $mapping = array(
            'number_required' => 'isNumberRequired',
            'enabled' => 'isEnabled',
            'capital_letter_required' => 'isCapitalLetterRequired',
            'min_length' => 'getMinLength',
        );

        foreach ($mapping as $keyName=>$methodName) {
            if (isset($rawConfig[$keyName])) {
                \Phake::when($config)
                    ->{$methodName}()
                    ->thenReturn($rawConfig[$keyName])
                ;
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
            self::$container->get('validator'),
            '',
            self::$container->get('translator')
        );
    }
}