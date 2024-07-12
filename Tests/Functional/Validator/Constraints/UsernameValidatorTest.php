<?php

namespace Modera\SecurityBundle\Tests\Functional\Validator\Constraints;

use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Modera\SecurityBundle\Validator\Constraints\UsernameValidator;
use Modera\SecurityBundle\Validator\Constraints\Username;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2017 Modera Foundation
 */
class UsernameValidatorTest extends FunctionalTestCase
{
    public function testValidate()
    {
        $context = $this->createContext();
        $validator = new UsernameValidator();
        $validator->initialize($context);

        $validator->validate('john.doe', new Username());
        $this->assertEquals(0, count($context->getViolations()));

        // ---

        $context = $this->createContext();
        $validator = new UsernameValidator();
        $validator->initialize($context);

        $validator->validate('<john.doe>', new Username());
        $this->assertEquals(1, count($context->getViolations()));

        // ---

        $context = $this->createContext();
        $validator = new UsernameValidator();
        $validator->initialize($context);

        $validator->validate('john@doe', new Username());
        $this->assertEquals(0, count($context->getViolations()));

        // ---

        $context = $this->createContext();
        $validator = new UsernameValidator();
        $validator->initialize($context);

        $validator->validate('<john@doe>', new Username());
        $this->assertEquals(1, count($context->getViolations()));
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
