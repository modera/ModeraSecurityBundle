<?php

namespace Modera\SecurityBundle\PasswordStrength;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
interface PasswordConfigInterface
{
    const LETTER_REQUIRED_TYPE_CAPITAL = 'capital';
    const LETTER_REQUIRED_TYPE_NON_CAPITAL = 'non_capital';
    const LETTER_REQUIRED_TYPE_CAPITAL_OR_NON_CAPITAL = 'capital_or_non_capital';
    const LETTER_REQUIRED_TYPE_CAPITAL_AND_NON_CAPITAL = 'capital_and_non_capital';

    const LETTER_REQUIRED_TYPES = array(
        self::LETTER_REQUIRED_TYPE_CAPITAL,
        self::LETTER_REQUIRED_TYPE_NON_CAPITAL,
        self::LETTER_REQUIRED_TYPE_CAPITAL_OR_NON_CAPITAL,
        self::LETTER_REQUIRED_TYPE_CAPITAL_AND_NON_CAPITAL,
    );

    /**
     * @return bool
     */
    public function isEnabled();

    /**
     * @return integer
     */
    public function getMinLength();

    /**
     * @return bool
     */
    public function isNumberRequired();

    /**
     * @return bool
     */
    public function isLetterRequired();

    /**
     * @return string
     */
    public function getLetterRequiredType();

    /**
     * @return integer
     */
    public function getRotationPeriodInDays();
}