<?php

namespace Modera\SecurityBundle\PasswordStrength;

use Modera\SecurityBundle\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @since 2.56.0
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class PasswordManager
{
    /**
     * @var PasswordConfigInterface
     */
    private $passwordConfig;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    private $validator;

    /**
     * @internal Use container service instead
     *
     * @param PasswordConfigInterface $passwordConfig
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param ValidatorInterface $validator
     */
    public function __construct(
        PasswordConfigInterface $passwordConfig,
        UserPasswordEncoderInterface $passwordEncoder,
        ValidatorInterface $validator
    )
    {
        $this->passwordConfig = $passwordConfig;
        $this->passwordEncoder = $passwordEncoder;
        $this->validator = $validator;
    }

    /**
     * Checks if it is allowed to use $plainPassword according to password rotation config. For example,
     * if rotation period is 30 days and password "foobar123" has been set within this period then
     * the method will return FALSE.
     *
     * @param User $user
     * @param string $plainPassword
     *
     * @return bool
     */
    public function hasPasswordAlreadyBeenUsedWithinLastRotationPeriod(User $user, $plainPassword)
    {
        if ($this->isPasswordRotationTurnedOff()) {
            return false;
        }

        $meta = $user->getMeta();
        if (!isset($meta['modera_security']['used_passwords'])) {
            return false; // if here we return TRUE then it won't be possible to create new users
        }

        $encodedPassword = $this->passwordEncoder->encodePassword($user, $plainPassword);

        $now = new \DateTime('now');
        foreach ($meta['modera_security']['used_passwords'] as $oldPasswordChangeTimestamp=>$oldEncodedPassword) {
            if ($encodedPassword == $oldEncodedPassword) {
                $oldPasswordChangeTime = new \DateTime();
                $oldPasswordChangeTime->setTimestamp($oldPasswordChangeTimestamp);

                $daysPassed = $now->diff($oldPasswordChangeTime)->days;
                if ($daysPassed < $this->passwordConfig->getRotationPeriodInDays()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param string $plainPassword
     *
     * @return ConstraintViolation[]
     */
    public function validatePassword($plainPassword)
    {
        return $this->validator->validate($plainPassword, new StrongPassword());
    }

    /**
     * Validates $plainPassword, verifies its against possibly configuration rotation if everything's fine then
     * it encodes it and updates $user, if some problems were detected then exception will be thrown.
     *
     * @throws BadPasswordException
     *
     * @param User $user
     *
     * @param $plainPassword
     */
    public function encodeAndSetPassword(User $user, $plainPassword)
    {
        if (!$this->isPasswordRotationTurnedOff()) {
            if ($this->hasPasswordAlreadyBeenUsedWithinLastRotationPeriod($user, $plainPassword)) {
                throw new BadPasswordException(sprintf(
                    'Given password cannot be used because it has been already used in last %d days.',
                    $this->passwordConfig->getRotationPeriodInDays()
                ));
            }
        }

        $violations = $this->validatePassword($plainPassword);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }

            throw new BadPasswordException('Given password failed validation: '.implode(', ', $errors));
        }

        $meta = $user->getMeta();
        if (!isset($meta['modera_security']['used_passwords'])) {
            if (!isset($meta['modera_security'])) {
                $meta['modera_security'] = array();
            }
            $meta['modera_security']['used_passwords'] = array();
        }

        $encodedPassword = $this->passwordEncoder->encodePassword($user, $plainPassword);
        $user->setPassword($encodedPassword);

        $meta['modera_security']['used_passwords'][time()] = $encodedPassword;
        $user->setMeta($meta);
    }

    /**
     * Returns TRUE if it is time to change user's password already.
     *
     * @param User $user
     *
     * @return bool
     */
    public function isTimeToRotatePassword(User $user)
    {
        if ($this->isPasswordRotationTurnedOff()) {
            return false;
        }

        $meta = $user->getMeta();
        if (!isset($meta['modera_security']['used_passwords'])) {
            return true;
        }

        $usedPasswords = $meta['modera_security']['used_passwords'];
        if (count($usedPasswords) == 0) {
            return true;
        }

        end($usedPasswords);
        $lastTimePasswordChangeDateTime = new \DateTime('now');
        $lastTimePasswordChangeDateTime->setTimestamp(key($usedPasswords));

        $now = new \DateTime('now');
        return $now->diff($lastTimePasswordChangeDateTime)->days > $this->passwordConfig->getRotationPeriodInDays();
    }

    /**
     * @param User|null $user
     */
    public function generatePassword(User $user = null)
    {
        while (true) {
            $plainPassword = '';
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            for ($i = 0; $i < $this->passwordConfig->getMinLength(); ++$i) {
                $plainPassword .= $characters[rand(0, strlen($characters) - 1)];
            }

            if ($this->passwordConfig->isNumberRequired() && !preg_match('/[0-9]/', $plainPassword)) {
                continue;
            }
            if ($this->passwordConfig->isCapitalLetterRequired() && !preg_match('/[A-Z]/', $plainPassword)) {
                continue;
            }

            return $plainPassword;
        }
    }

    private function isPasswordRotationTurnedOff()
    {
        return $this->passwordConfig->getRotationPeriodInDays() === false;
    }
}