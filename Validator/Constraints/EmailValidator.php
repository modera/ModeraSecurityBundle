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
    public const PATTERN = '/^[a-zA-Z0-9.!#$%&\'*+\\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$/';

    public function validate($value, Constraint $constraint): void
    {
        $errorMsg = 'This value is not a valid email address.';
        if (\class_exists('Modera\FoundationBundle\Translation\T')) {
            $errorMsg = T::trans($errorMsg);
        }

        $regex = new Constraints\Regex(['pattern' => self::PATTERN]);
        $regex->message = $errorMsg;

        $this->subValidate(
            new Constraints\RegexValidator(),
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
