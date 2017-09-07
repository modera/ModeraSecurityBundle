<?php

namespace Modera\SecurityBundle\PasswordStrength;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
interface PasswordConfigInterface
{
    /**
     * @deprecated Will be removed in 3.0, it is added as a BC layer to avoid breaking default
     * password changing flow
     *
     * @return boolean
     */
    public function isEnabled();

    /**
     * @return integer
     */
    public function getMinLength();

    /**
     * @return boolean
     */
    public function isNumberRequired();

    /**
     * @return boolean
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