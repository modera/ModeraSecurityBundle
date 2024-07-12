<?php

namespace Modera\SecurityBundle\PasswordStrength;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
interface PasswordConfigInterface
{
    public const LETTER_REQUIRED_TYPE_CAPITAL = 'capital';
    public const LETTER_REQUIRED_TYPE_NON_CAPITAL = 'non_capital';
    public const LETTER_REQUIRED_TYPE_CAPITAL_OR_NON_CAPITAL = 'capital_or_non_capital';
    public const LETTER_REQUIRED_TYPE_CAPITAL_AND_NON_CAPITAL = 'capital_and_non_capital';

    public const LETTER_REQUIRED_TYPES = [
        self::LETTER_REQUIRED_TYPE_CAPITAL,
        self::LETTER_REQUIRED_TYPE_NON_CAPITAL,
        self::LETTER_REQUIRED_TYPE_CAPITAL_OR_NON_CAPITAL,
        self::LETTER_REQUIRED_TYPE_CAPITAL_AND_NON_CAPITAL,
    ];

    public function isEnabled(): bool;

    public function getMinLength(): int;

    public function isNumberRequired(): bool;

    public function isLetterRequired(): bool;

    public function getLetterRequiredType(): string;

    public function getRotationPeriodInDays(): ?int;
}
