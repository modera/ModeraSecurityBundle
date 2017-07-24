<?php

namespace Modera\SecurityBundle\PasswordStrength;

use Modera\SecurityBundle\Entity\User;

/**
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

    }
}