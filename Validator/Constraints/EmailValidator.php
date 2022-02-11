<?php

namespace Modera\SecurityBundle\Validator\Constraints;

use Modera\FoundationBundle\Translation\T;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @internal
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2017 Modera Foundation
 */
class EmailValidator extends ConstraintValidator
{
    public const PATTERN = Constraints\EmailValidator::PATTERN_HTML5;

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $errorMsg = 'This value is not a valid email address.';
        if (class_exists('Modera\FoundationBundle\Translation\T')) {
            $errorMsg = T::trans($errorMsg);
        }

        $regex = new Constraints\Regex(array('pattern' => self::PATTERN));
        $regex->message = $errorMsg;

        $this->subValidate(
            new Constraints\RegexValidator(),
            $value,
            $regex
        );
    }

    /**
     * @param ConstraintValidator $validator
     * @param mixed $value
     * @param Constraint $constraint
     */
    private function subValidate(ConstraintValidator $validator, $value, Constraint $constraint)
    {
        $this->context->setConstraint($constraint);
        $validator->initialize($this->context);
        $validator->validate($value, $constraint);
    }
}
