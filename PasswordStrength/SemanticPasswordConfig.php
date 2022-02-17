<?php

namespace Modera\SecurityBundle\PasswordStrength;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class SemanticPasswordConfig implements PasswordConfigInterface
{
    /**
     * @var array
     */
    private $semanticConfig = array();

    /**
     * @param array $bundleSemanticConfig
     */
    public function __construct(array $bundleSemanticConfig)
    {
        $this->semanticConfig = $bundleSemanticConfig['password_strength'];
    }

    /**
     * {@inheritdoc}
     */
    public function getMinLength()
    {
        return $this->semanticConfig['min_length'];
    }

    /**
     * {@inheritdoc}
     */
    public function isNumberRequired()
    {
        return $this->isEnabled() ? $this->semanticConfig['number_required'] : false;
    }

    /**
     * {@inheritdoc}
     */
    public function isLetterRequired()
    {
        return $this->isEnabled() ? false !== $this->semanticConfig['letter_required'] : false;
    }

    /**
     * {@inheritdoc}
     */
    public function getLetterRequiredType()
    {
        return $this->semanticConfig['letter_required'];
    }

    /**
     * {@inheritdoc}
     */
    public function getRotationPeriodInDays()
    {
        return $this->isEnabled() ? $this->semanticConfig['rotation_period'] : false;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->semanticConfig['enabled'];
    }
}
