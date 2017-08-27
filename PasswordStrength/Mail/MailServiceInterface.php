<?php

namespace Modera\SecurityBundle\PasswordStrength\Mail;

use Modera\SecurityBundle\Entity\User;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
interface MailServiceInterface
{
    /**
     * @param User $user
     * @param $plainPassword
     *
     * @return array|bool
     */
    public function sendPassword(User $user, $plainPassword);
}