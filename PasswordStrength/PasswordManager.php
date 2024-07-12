<?php

namespace Modera\SecurityBundle\PasswordStrength;

use Modera\SecurityBundle\Entity\User;
use Modera\SecurityBundle\PasswordStrength\Mail\MailServiceInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class PasswordManager
{
    private PasswordConfigInterface $passwordConfig;

    private UserPasswordHasherInterface $passwordHasher;

    private ValidatorInterface $validator;

    private MailServiceInterface $mailService;

    /**
     * @internal Use container service instead
     */
    public function __construct(
        PasswordConfigInterface $passwordConfig,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
        MailServiceInterface $mailService
    ) {
        $this->passwordConfig = $passwordConfig;
        $this->passwordHasher = $passwordHasher;
        $this->validator = $validator;
        $this->mailService = $mailService;
    }

    /**
     * Checks if it is allowed to use $plainPassword according to password rotation config. For example,
     * if rotation period is 30 days and password "foobar123" has been set within this period then
     * the method will return FALSE.
     */
    public function hasPasswordAlreadyBeenUsedWithinLastRotationPeriod(User $user, string $plainPassword): bool
    {
        if ($this->isPasswordRotationTurnedOff()) {
            return false;
        }

        $meta = $user->getMeta();
        if (!\is_array($meta['modera_security'] ?? null) || !\is_array($meta['modera_security']['used_passwords'] ?? null)) {
            return false; // if here we return TRUE then it won't be possible to create new users
        }

        $encodedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);

        $now = new \DateTime('now');
        foreach ($meta['modera_security']['used_passwords'] as $oldPasswordChangeTimestamp => $oldEncodedPassword) {
            if ($encodedPassword === $oldEncodedPassword) {
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

    public function validatePassword(string $plainPassword): ConstraintViolationListInterface
    {
        return $this->validator->validate($plainPassword, new StrongPassword());
    }

    /**
     * Validates $plainPassword, verifies it's against possibly configuration rotation if everything's fine then
     * it encodes it and updates $user, if some problems were detected then exception will be thrown.
     *
     * NB! Changes are not automatically persisted into database, so you need to flush UoW manually.
     *
     * @throws BadPasswordException
     */
    public function encodeAndSetPassword(User $user, string $plainPassword): void
    {
        if (!$this->isPasswordRotationTurnedOff()) {
            if ($this->hasPasswordAlreadyBeenUsedWithinLastRotationPeriod($user, $plainPassword)) {
                $error = \sprintf(
                    'Given password cannot be used because it has been already used in last %d days.',
                    $this->passwordConfig->getRotationPeriodInDays()
                );
                $e = new BadPasswordException($error);
                $e->setErrors([$error]);
                throw $e;
            }
        }

        $violations = $this->validatePassword($plainPassword);
        if (\count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }

            $e = new BadPasswordException(\implode(' ', $errors));
            $e->setErrors($errors);
            throw $e;
        }

        $encodedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($encodedPassword);

        $meta = $user->getMeta();
        if (!\is_array($meta['modera_security'] ?? null)) {
            $meta['modera_security'] = [];
        }

        if (!\is_array($meta['modera_security']['used_passwords'] ?? null) || $this->isPasswordRotationTurnedOff()) {
            $meta['modera_security']['used_passwords'] = [];
        }

        // remove outdated passwords
        if (!$this->isPasswordRotationTurnedOff()) {
            $now = new \DateTime('now');
            foreach ($meta['modera_security']['used_passwords'] as $oldPasswordChangeTimestamp => $oldEncodedPassword) {
                $oldPasswordChangeTime = new \DateTime();
                $oldPasswordChangeTime->setTimestamp($oldPasswordChangeTimestamp);

                $daysPassed = $now->diff($oldPasswordChangeTime)->days;
                if ($daysPassed > $this->passwordConfig->getRotationPeriodInDays()) {
                    unset($meta['modera_security']['used_passwords'][$oldPasswordChangeTimestamp]);
                }
            }
        }

        $meta['modera_security']['used_passwords'][\time()] = $encodedPassword;
        unset($meta['modera_security']['force_password_rotation']);

        $user->setMeta($meta);
    }

    /**
     * Returns TRUE if it is time to change user's password already.
     */
    public function isItTimeToRotatePassword(User $user): bool
    {
        if ($this->isPasswordRotationTurnedOff()) {
            return false;
        }

        $meta = $user->getMeta();
        if (!\is_array($meta['modera_security'] ?? null)) {
            $meta['modera_security'] = [];
        }

        if (isset($meta['modera_security']['force_password_rotation'])
            && true === $meta['modera_security']['force_password_rotation']) {
            return true;
        }

        if (!isset($meta['modera_security']['used_passwords'])) {
            return true;
        }

        $usedPasswords = $meta['modera_security']['used_passwords'];
        if (!\is_array($usedPasswords)) {
            $usedPasswords = [];
        }

        if (0 === \count($usedPasswords)) {
            return true;
        }

        \end($usedPasswords);
        $lastTimePasswordChangeDateTime = new \DateTime('now');
        $lastTimePasswordChangeDateTime->setTimestamp(key($usedPasswords));

        $now = new \DateTime('now');

        return $now->diff($lastTimePasswordChangeDateTime)->days > $this->passwordConfig->getRotationPeriodInDays();
    }

    public function generatePassword(?User $user = null): string
    {
        while (true) {
            $plainPassword = '';
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            for ($i = 0; $i < $this->passwordConfig->getMinLength(); ++$i) {
                $plainPassword .= $characters[\rand(0, \strlen($characters) - 1)];
            }

            if ($this->passwordConfig->isNumberRequired() && !\preg_match('/[0-9]/', $plainPassword)) {
                continue;
            }
            if ($this->passwordConfig->isLetterRequired()) {
                $continue = false;
                switch ($this->passwordConfig->getLetterRequiredType()) {
                    case PasswordConfigInterface::LETTER_REQUIRED_TYPE_CAPITAL_OR_NON_CAPITAL:
                        $continue = !\preg_match('/[A-Za-z]/', $plainPassword);
                        break;
                    case PasswordConfigInterface::LETTER_REQUIRED_TYPE_CAPITAL_AND_NON_CAPITAL:
                        $continue = !\preg_match('/(?=.*[A-Z])(?=.*[a-z])/', $plainPassword);
                        break;
                    case PasswordConfigInterface::LETTER_REQUIRED_TYPE_CAPITAL:
                        $continue = !\preg_match('/[A-Z]/', $plainPassword);
                        break;
                    case PasswordConfigInterface::LETTER_REQUIRED_TYPE_NON_CAPITAL:
                        $continue = !\preg_match('/[a-z]/', $plainPassword);
                        break;
                }

                if ($continue) {
                    continue;
                }
            }

            if ($user && $this->hasPasswordAlreadyBeenUsedWithinLastRotationPeriod($user, $plainPassword)) {
                continue;
            }

            return $plainPassword;
        }
    }

    /**
     * When a password is sent over email then the first time user logins using it he/she will
     * be force to change it.
     */
    public function encodeAndSetPasswordAndThenEmailIt(User $user, string $plainPassword): void
    {
        $this->encodeAndSetPassword($user, $plainPassword);

        $meta = $user->getMeta();
        if (!\is_array($meta['modera_security'] ?? null)) {
            $meta['modera_security'] = [];
        }
        $meta['modera_security']['force_password_rotation'] = true;
        $user->setMeta($meta);

        $this->mailService->sendPassword($user, $plainPassword);
    }

    private function isPasswordRotationTurnedOff(): bool
    {
        return null === $this->passwordConfig->getRotationPeriodInDays();
    }
}
