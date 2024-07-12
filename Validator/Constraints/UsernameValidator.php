<?php

namespace Modera\SecurityBundle\Validator\Constraints;

use Modera\FoundationBundle\Translation\T;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\RegexValidator;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @internal
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2017 Modera Foundation
 */
class UsernameValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        $errorMsg = 'This value can only contain characters that are allowed in e-mail addresses.';
        if (class_exists('Modera\FoundationBundle\Translation\T')) {
            $errorMsg = T::trans($errorMsg);
        }

        $regex = new Regex(['pattern' => '/^[a-zA-Z0-9\._!#$%&’*+\/=?^`{|}~@-]+$/']);
        $regex->message = $errorMsg;

        $this->subValidate(
            new RegexValidator(),
            $value,
            $regex
        );
    }

    /**
     * @param mixed $value Mixed value
     */
    private function subValidate(ConstraintValidator $validator, $value, Constraint $constraint): void
    {
        $this->context->setConstraint($constraint);
        $validator->initialize($this->context);
        $validator->validate($value, $constraint);
    }
}
