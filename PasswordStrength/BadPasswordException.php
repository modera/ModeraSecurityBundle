<?php

namespace Modera\SecurityBundle\PasswordStrength;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class BadPasswordException extends \RuntimeException
{
    private $errors = array();

    /**
     * @param array $errors
     */
    public function setErrors(array $errors = array())
    {
        $this->errors = $errors;
    }

    /**
     * @param array $errors
     */
    public function getErrors()
    {
        return $this->errors;
    }
}