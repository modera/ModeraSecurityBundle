<?php

namespace Modera\SecurityBundle\PasswordStrength;

use Modera\SecurityBundle\Entity\User;

/**
 * @since 2.56.0
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class PasswordGenerator
{
    /**
     * @var PasswordConfigInterface
     */
    private $passwordConfig;

    /**
     * @param PasswordConfigInterface $passwordConfig
     */
    public function __construct(PasswordConfigInterface $passwordConfig)
    {
        $this->passwordConfig = $passwordConfig;
    }

    /**
     * @param User|null $user
     */
    public function generatePassword(User $user = null)
    {
        while (true) {
            $plainPassword = '';
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            for ($i = 0; $i < $this->passwordConfig->getMinLength(); ++$i) {
                $plainPassword .= $characters[rand(0, strlen($characters) - 1)];
            }

            if ($this->passwordConfig->isNumberRequired() && !preg_match('/[0-9]/', $plainPassword)) {
                continue;
            }
            if ($this->passwordConfig->isCapitalLetterRequired() && !preg_match('/[A-Z]/', $plainPassword)) {
                continue;
            }

            return $plainPassword;
        }
    }
}