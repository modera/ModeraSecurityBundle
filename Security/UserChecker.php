<?php

namespace Modera\SecurityBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface as CoreUserInterface;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Modera\SecurityBundle\Model\UserInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2022 Modera Foundation
 */
class UserChecker implements UserCheckerInterface
{
    /**
     * {@inheritdoc}
     */
    public function checkPreAuth(CoreUserInterface $user): void
    {
        if (!$user instanceof UserInterface) {
            return;
        }

        if (!$user->isEnabled()) {
            $ex = new DisabledException('User account is disabled.');
            $ex->setUser($user);
            throw $ex;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function checkPostAuth(CoreUserInterface $user): void
    {
        if (!$user instanceof UserInterface) {
            return;
        }
    }
}
