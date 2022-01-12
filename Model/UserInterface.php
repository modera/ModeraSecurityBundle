<?php

namespace Modera\SecurityBundle\Model;

use Symfony\Component\Security\Core\Exception\DisabledException;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2014 Modera Foundation
 */
interface UserInterface
{
    const GENDER_MALE = 'm';
    const GENDER_FEMALE = 'f';

    const STATE_NEW = 0;
    const STATE_ACTIVE = 1;

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @return string
     */
    public function getFirstName();

    /**
     * @return string
     */
    public function getLastName();

    /**
     * @return string
     */
    public function getMiddleName();

    /**
     * @return string
     */
    public function getFullName();

    /**
     * @return string
     */
    public function getGender();

    /**
     * @return string
     */
    public function getState();

    /**
     * @return \DateTime|null
     */
    public function getLastLogin();

    /**
     * Checks whether the user is enabled.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a DisabledException and prevent login.
     *
     * @return bool true if the user is enabled, false otherwise
     *
     * @see DisabledException
     */
    public function isEnabled();
}
