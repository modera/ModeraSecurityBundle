<?php

namespace Modera\SecurityBundle\Model;

use Symfony\Component\Security\Core\Exception\DisabledException;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 *
 * @method ?string getUsername()
 */
interface UserInterface
{
    public const GENDER_MALE = 'm';
    public const GENDER_FEMALE = 'f';

    public const STATE_NEW = 0;
    public const STATE_ACTIVE = 1;

    public function getEmail(): ?string;

    // public function getUsername(): ?string;

    public function getPersonalId(): ?string;

    public function getFirstName(): ?string;

    public function getLastName(): ?string;

    public function getMiddleName(): ?string;

    public function getFullName(string $pattern = 'first last'): ?string;

    public function getGender(): ?string;

    public function getState(): int;

    public function getLastLogin(): ?\DateTimeInterface;

    /**
     * Checks whether the user is enabled.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a DisabledException and prevent login.
     *
     * true if the user is enabled, false otherwise
     *
     * @see DisabledException
     */
    public function isEnabled(): bool;
}
