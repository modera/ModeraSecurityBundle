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
    /**
     * @var PasswordConfigInterface
     */
    private $config;

    /**
     * @param PasswordConfigInterface $config
     */
    public function __construct(PasswordConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        if ($this->config->getMinLength() > 0) {
            $lengthConstr = new Length(array('min' => $this->config->getMinLength()));

            $this->context->setConstraint($lengthConstr);
            $lengthValidator = new LengthValidator();
            $lengthValidator->initialize($this->context);
            $lengthValidator->validate($value, $lengthConstr);
        }

        if ($this->config->isNumberRequired()) {
            $errorMsg = 'Password must contain at least one number character.';
            if (class_exists('Modera\FoundationBundle\Translation\T')) {
                $errorMsg = T::trans($errorMsg);
            }

            $regexConstr = new Regex(array('pattern' => '/[0-9]/'));
            $regexConstr->message = $errorMsg;

            $this->context->setConstraint($regexConstr);
            $lengthValidator = new RegexValidator();
            $lengthValidator->initialize($this->context);
            $lengthValidator->validate($value, $regexConstr);
        }

        if ($this->config->isLetterRequired()) {
            switch ($this->config->getLetterRequiredType()) {
                case 'capital_or_non_capital':
                    $pattern = '/[A-Za-z]/';
                    $errorMsg = 'Password must contain at least one letter.';
                    if (class_exists('Modera\FoundationBundle\Translation\T')) {
                        $errorMsg = T::trans($errorMsg);
                    }
                    break;
                case 'capital_and_non_capital':
                    $pattern = '/(?=.*[A-Z])(?=.*[a-z])/';
                    $errorMsg = 'Password must contain at least one capital and one non-capital letter.';
                    if (class_exists('Modera\FoundationBundle\Translation\T')) {
                        $errorMsg = T::trans($errorMsg);
                    }
                    break;
                case 'capital':
                    $pattern = '/[A-Z]/';
                    $errorMsg = 'Password must contain at least one capital letter.';
                    if (class_exists('Modera\FoundationBundle\Translation\T')) {
                        $errorMsg = T::trans($errorMsg);
                    }
                    break;
                case 'non_capital':
                    $pattern = '/[a-z]/';
                    $errorMsg = 'Password must contain at least one non-capital letter.';
                    if (class_exists('Modera\FoundationBundle\Translation\T')) {
                        $errorMsg = T::trans($errorMsg);
                    }
                    break;
            }

            $regexConstr = new Regex(array('pattern' => $pattern));
            $regexConstr->message = $errorMsg;

            $this->context->setConstraint($regexConstr);
            $lengthValidator = new RegexValidator();
            $lengthValidator->initialize($this->context);
            $lengthValidator->validate($value, $regexConstr);
        }
    }
}