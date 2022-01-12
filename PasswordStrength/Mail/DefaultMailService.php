<?php

namespace Modera\SecurityBundle\PasswordStrength\Mail;

use Modera\SecurityBundle\Entity\User;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2022 Modera Foundation
 */
class DefaultMailService implements MailServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function sendPassword(User $user, $plainPassword)
    {
        // do nothing
    }
}
