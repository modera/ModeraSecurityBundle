<?php

namespace Modera\SecurityBundle\PasswordStrength;

use Modera\FoundationBundle\Translation\T;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LengthValidator;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\RegexValidator;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @see StrongPassword class doc.
 *
 * @internal
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class StrongPasswordValidator extends ConstraintValidator
{
    private PasswordConfigInterface $config;

    public function __construct(PasswordConfigInterface $config)
    {
        $this->config = $config;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        if ($this->config->getMinLength() > 0) {
            $this->subValidate(
                new LengthValidator(),
                $value,
                new Length(['min' => $this->config->getMinLength()])
            );
        }

        if ($this->config->isNumberRequired()) {
            $errorMsg = 'Password must contain at least one number character.';
            if (\class_exists('Modera\FoundationBundle\Translation\T')) {
                $errorMsg = T::trans($errorMsg);
            }

            $regexConstraint = new Regex(['pattern' => '/[0-9]/']);
            $regexConstraint->message = $errorMsg;

            $this->subValidate(
                new RegexValidator(),
                $value,
                $regexConstraint
            );
        }

        if ($this->config->isLetterRequired()) {
            switch ($type = $this->config->getLetterRequiredType()) {
                case PasswordConfigInterface::LETTER_REQUIRED_TYPE_CAPITAL_OR_NON_CAPITAL:
                    $pattern = '/[A-Za-z]/';
                    $errorMsg = 'Password must contain at least one letter.';
                    if (\class_exists('Modera\FoundationBundle\Translation\T')) {
                        $errorMsg = T::trans($errorMsg);
                    }
                    break;
                case PasswordConfigInterface::LETTER_REQUIRED_TYPE_CAPITAL_AND_NON_CAPITAL:
                    $pattern = '/(?=.*[A-Z])(?=.*[a-z])/';
                    $errorMsg = 'Password must contain at least one capital and one non-capital letter.';
                    if (\class_exists('Modera\FoundationBundle\Translation\T')) {
                        $errorMsg = T::trans($errorMsg);
                    }
                    break;
                case PasswordConfigInterface::LETTER_REQUIRED_TYPE_CAPITAL:
                    $pattern = '/[A-Z]/';
                    $errorMsg = 'Password must contain at least one capital letter.';
                    if (\class_exists('Modera\FoundationBundle\Translation\T')) {
                        $errorMsg = T::trans($errorMsg);
                    }
                    break;
                case PasswordConfigInterface::LETTER_REQUIRED_TYPE_NON_CAPITAL:
                    $pattern = '/[a-z]/';
                    $errorMsg = 'Password must contain at least one non-capital letter.';
                    if (\class_exists('Modera\FoundationBundle\Translation\T')) {
                        $errorMsg = T::trans($errorMsg);
                    }
                    break;
                default:
                    throw new \RuntimeException(\sprintf('Unsupported type: %s', $type));
            }

            $regexConstraint = new Regex(['pattern' => $pattern]);
            $regexConstraint->message = $errorMsg;

            $this->subValidate(
                new RegexValidator(),
                $value,
                $regexConstraint
            );
        }
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
