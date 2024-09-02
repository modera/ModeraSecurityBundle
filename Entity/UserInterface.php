<?php

namespace Modera\SecurityBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Modera\SecurityBundle\Model\UserInterface as ModeraUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\LegacyPasswordAuthenticatedUserInterface as PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2022 Modera Foundation
 */
interface UserInterface extends EquatableInterface, PasswordAuthenticatedUserInterface, SymfonyUserInterface, ModeraUserInterface
{
    public function getId(): ?int;

    public function isActive(): bool;

    public function setActive(bool $isActive): void;

    /**
     * @return Collection<int, Group>
     */
    public function getGroups(): Collection;

    /**
     * @return Collection<int, Permission>
     */
    public function getPermissions(): Collection;

    /**
     * @return array<string, mixed>
     */
    public function getMeta(): array;
}
