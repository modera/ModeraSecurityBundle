<?php

namespace Modera\SecurityBundle\PasswordStrength;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class SemanticPasswordConfig implements PasswordConfigInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $semanticConfig = [];

    /**
     * @param array<string, mixed> $bundleSemanticConfig
     */
    public function __construct(array $bundleSemanticConfig)
    {
        /** @var array<string, mixed> $semanticConfig */
        $semanticConfig = $bundleSemanticConfig['password_strength'];
        $this->semanticConfig = $semanticConfig;
    }

    public function getMinLength(): int
    {
        /** @var int $minLength */
        $minLength = $this->semanticConfig['min_length'];

        return $minLength;
    }

    public function isNumberRequired(): bool
    {
        return $this->isEnabled() && false !== $this->semanticConfig['number_required'];
    }

    public function isLetterRequired(): bool
    {
        return $this->isEnabled() && false !== $this->semanticConfig['letter_required'];
    }

    public function getLetterRequiredType(): string
    {
        if (\is_string($this->semanticConfig['letter_required'])) {
            return $this->semanticConfig['letter_required'];
        }

        return PasswordConfigInterface::LETTER_REQUIRED_TYPE_CAPITAL_OR_NON_CAPITAL;
    }

    public function getRotationPeriodInDays(): ?int
    {
        if ($this->isEnabled() && \is_int($this->semanticConfig['rotation_period'])) {
            return $this->semanticConfig['rotation_period'];
        }

        return null;
    }

    public function isEnabled(): bool
    {
        return true === $this->semanticConfig['enabled'];
    }
}
