<?php

namespace Modera\SecurityBundle\Model;

/**
 * A higher level of abstraction for Symfony security roles, adds some additional information to roles
 * to make them more manageable by non-technical people.
 *
 * @author Sergei Lissovski <sergei.lissovski@modera.org>
 */
interface PermissionInterface
{
    /**
     * Returns a Symfony security role that this permission represents. You can use this role with
     * implementations of \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface to check
     * if user has access to perform certain operation.
     */
    public function getRole(): string;

    /**
     * A human understandable name for this permission, for example - Access "Admin" section.
     */
    public function getName(): string;

    /**
     * A human understandable description for this permission, for example -
     * "This permission is used to allow a user see a section in the menu".
     */
    public function getDescription(): ?string;

    /**
     * @see PermissionCategoryInterface::getTechnicalName()
     *
     * A "technical name" of a PermissionCategory
     */
    public function getCategory(): ?string;
}
