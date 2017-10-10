<?php

namespace Modera\SecurityBundle\Tests\Functional\Validator\Constraints;

use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Modera\SecurityBundle\Validator\Constraints\EmailValidator;
use Modera\SecurityBundle\Validator\Constraints\Email;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2017 Modera Foundation
 */
class EmailValidatorTest extends FunctionalTestCase
{
    public function testValidate()
    {
        $context = $this->createContext();
        $validator = new EmailValidator();
        $validator->initialize($context);

        $validator->validate('user@email.good', new Email());
        $this->assertEquals(0, count($context->getViolations()));

        // ---

        $context = $this->createContext();
        $validator = new EmailValidator();
        $validator->initialize($context);

        $validator->validate('<user@email.bad>', new Email());
        $this->assertEquals(1, count($context->getViolations()));
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