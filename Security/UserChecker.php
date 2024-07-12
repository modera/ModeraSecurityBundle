<?php

namespace Modera\SecurityBundle\Security;

use Modera\SecurityBundle\Entity\UserInterface;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface as CoreUserInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2022 Modera Foundation
 */
class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(CoreUserInterface $user): void
    {
        if (!$user instanceof UserInterface) {
            return;
        }

        if (!$user->isActive()) {
            $ex = new DisabledException('User account is disabled.');
            $ex->setUser($user);
            throw $ex;
        }
    }

    public function checkPostAuth(CoreUserInterface $user): void
    {
        if (!$user instanceof UserInterface) {
            return;
        }
    }
}
