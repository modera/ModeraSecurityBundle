<?php

namespace Modera\SecurityBundle\RootUserHandling;

use Modera\SecurityBundle\Entity\UserInterface;

/**
 * Class is responsible to determine if a user currently being authenticated is so called "root". Root
 * must never be deleted, and it must have all privileges.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2014 Modera Foundation
 */
interface RootUserHandlerInterface
{
    /**
     * Method is responsible to determine if a user that is about to be authenticated is root user.
     */
    public function isRootUser(UserInterface $user): bool;

    /**
     * Must return root user.
     */
    public function getUser(): UserInterface;

    /**
     * Must return roles names that root user will have.
     *
     * @return string[]
     */
    public function getRoles(): array;
}
