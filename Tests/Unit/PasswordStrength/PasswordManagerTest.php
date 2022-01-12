<?php

namespace Modera\SecurityBundle\Tests\Unit\PasswordStrength;

use Modera\SecurityBundle\Entity\User;
use Modera\SecurityBundle\PasswordStrength\BadPasswordException;
use Modera\SecurityBundle\PasswordStrength\Mail\MailServiceInterface;
use Modera\SecurityBundle\PasswordStrength\PasswordConfigInterface;
use Modera\SecurityBundle\PasswordStrength\PasswordManager;
use Modera\SecurityBundle\PasswordStrength\StrongPassword;
use Modera\SecurityBundle\PasswordStrength\StrongPasswordValidator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserPasswordEncoderDummy implements UserPasswordEncoderInterface
{
    public $mapping = array();

    public function encodePassword(UserInterface $user, $plainPassword)
    {
        return $this->mapping[$plainPassword];
    }

    public function isPasswordValid(UserInterface $user, $raw)
    {
    }

    public function needsRehash(UserInterface $user): bool
    {
        return false;
    }
}

/**
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class PasswordManagerTest extends \PHPUnit\Framework\TestCase
{
    public function testHasPasswordAlreadyBeenUsedWithinLastRotationPeriod()
    {
        $passwordConfigMock = \Phake::mock(PasswordConfigInterface::class);
        \Phake::when($passwordConfigMock)
            ->getRotationPeriodInDays()
            ->thenReturn(90)
        ;

        $encoderDummy = new UserPasswordEncoderDummy();
        $encoderDummy->mapping = array(
            'foo' => 'encoded-foo',
            'bar' => 'encoded-bar',
            'baz' => 'encoded-baz',
            'yoyo' => 'encoded-yoyo',
        );

        $validatorMock = \Phake::mock(ValidatorInterface::class);
        \Phake::when($validatorMock)
            ->validate(\Phake::anyParameters())
            ->thenReturn([])
        ;

        $pm = new PasswordManager(
            $passwordConfigMock, $encoderDummy, $validatorMock, \Phake::mock(MailServiceInterface::class)
        );

        $user = new User();
        $this->assertFalse($pm->hasPasswordAlreadyBeenUsedWithinLastRotationPeriod($user, 1234));

        $user = new User();
        $user->setMeta(array(
            'modera_security' => array(
                'used_passwords' => array(
                    $this->createTimeWithDaysAgo(200) => 'encoded-foo',
                    $this->createTimeWithDaysAgo(91) => 'encoded-bar',
                    $this->createTimeWithDaysAgo(50) => 'encoded-baz',
                    $this->createTimeWithDaysAgo(10) => 'encoded-yoyo',
                ),
            ),
        ));

        $this->assertFalse($pm->hasPasswordAlreadyBeenUsedWithinLastRotationPeriod($user, 'foo'));
        $this->assertFalse($pm->hasPasswordAlreadyBeenUsedWithinLastRotationPeriod($user, 'bar'));
        $this->assertTrue($pm->hasPasswordAlreadyBeenUsedWithinLastRotationPeriod($user, 'baz'));
        $this->assertTrue($pm->hasPasswordAlreadyBeenUsedWithinLastRotationPeriod($user, 'yoyo'));
    }

    public function testIsItTimeToRotatePassword()
    {
        $passwordConfigMock = \Phake::mock(PasswordConfigInterface::class);
        \Phake::when($passwordConfigMock)
            ->getRotationPeriodInDays()
            ->thenReturn(90)
        ;

        $encoderDummy = new UserPasswordEncoderDummy();
        $encoderDummy->mapping = array(
            'foo' => 'encoded-foo',
            'bar' => 'encoded-bar',
            'baz' => 'encoded-baz',
            'yoyo' => 'encoded-yoyo',
        );

        $validatorMock = \Phake::mock(ValidatorInterface::class);
        \Phake::when($validatorMock)
            ->validate(\Phake::anyParameters())
            ->thenReturn([])
        ;

        $pm = new PasswordManager(
            $passwordConfigMock, $encoderDummy, $validatorMock, \Phake::mock(MailServiceInterface::class)
        );

        $this->assertTrue($pm->isItTimeToRotatePassword(new User()));

        $user = new User();
        $user->setMeta(array(
            'modera_security' => array(
                'used_passwords' => array(),
            ),
        ));

        $this->assertTrue($pm->isItTimeToRotatePassword($user));

        $user = new User();
        $user->setMeta(array(
            'modera_security' => array(
                'used_passwords' => array(
                    $this->createTimeWithDaysAgo(200) => 'encoded-foo',
                    $this->createTimeWithDaysAgo(100) => 'encoded-bar',
                ),
            ),
        ));

        $this->assertTrue($pm->isItTimeToRotatePassword($user));

        $user = new User();
        $user->setMeta(array(
            'modera_security' => array(
                'used_passwords' => array(
                    $this->createTimeWithDaysAgo(95) => 'encoded-foo',
                    $this->createTimeWithDaysAgo(60) => 'encoded-bar',
                ),
            ),
        ));

        $this->assertFalse($pm->isItTimeToRotatePassword($user));
    }

    public function testValidatePassword()
    {
        $passwordConfigMock = \Phake::mock(PasswordConfigInterface::class);
        $encoderDummy = new UserPasswordEncoderDummy();
        $validatorMock = \Phake::mock(ValidatorInterface::class);

        \Phake::when($validatorMock)
            ->validate('foo123', $this->isInstanceOf(StrongPassword::class))
            ->thenReturn('validation-result')
        ;

        $pm = new PasswordManager(
            $passwordConfigMock, $encoderDummy, $validatorMock, \Phake::mock(MailServiceInterface::class)
        );

        $this->assertEquals('validation-result', $pm->validatePassword('foo123'));
    }

    public function testEncodeAndSetPassword_happyPath()
    {
        $passwordConfigMock = \Phake::mock(PasswordConfigInterface::class);
        \Phake::when($passwordConfigMock)
            ->getRotationPeriodInDays()
            ->thenReturn(false)
        ;

        $encoderDummy = new UserPasswordEncoderDummy();
        $encoderDummy->mapping = array(
            'foo' => 'encoded-foo',
            'bar' => 'encoded-bar',
            'baz' => 'encoded-baz',
            'yoyo' => 'encoded-yoyo',
        );

        $validatorMock = \Phake::mock(ValidatorInterface::class);
        \Phake::when($validatorMock)
            ->validate(\Phake::anyParameters())
            ->thenReturn([])
        ;

        $pm = new PasswordManager(
            $passwordConfigMock, $encoderDummy, $validatorMock, \Phake::mock(MailServiceInterface::class)
        );

        $user = new User();
        $pm->encodeAndSetPassword($user, 'foo');

        $meta = $user->getMeta();
        $this->assertArrayHasKey('modera_security', $meta);
        $this->assertArrayHasKey('used_passwords', $meta['modera_security']);
        $this->assertEquals(1, count($meta['modera_security']['used_passwords']));
        $this->assertLessThan(10, array_keys($meta['modera_security']['used_passwords'])[0] - time());
        $usedPasswords = array_values($meta['modera_security']['used_passwords']);
        $this->assertEquals('encoded-foo', $usedPasswords[0]);
    }

    public function testEncodeAndSetPassword_forcePasswordRotationTracesRemoved()
    {
        $passwordConfigMock = \Phake::mock(PasswordConfigInterface::class);
        \Phake::when($passwordConfigMock)
            ->getRotationPeriodInDays()
            ->thenReturn(false)
        ;

        $encoderDummy = new UserPasswordEncoderDummy();
        $encoderDummy->mapping = array(
            'foo' => 'encoded-foo',
            'bar' => 'encoded-bar',
            'baz' => 'encoded-baz',
            'yoyo' => 'encoded-yoyo',
        );

        $validatorMock = \Phake::mock(ValidatorInterface::class);
        \Phake::when($validatorMock)
            ->validate(\Phake::anyParameters())
            ->thenReturn([])
        ;

        $pm = new PasswordManager(
            $passwordConfigMock, $encoderDummy, $validatorMock, \Phake::mock(MailServiceInterface::class)
        );

        $user = new User();
        $user->setMeta(array(
            'modera_security' => array(
                'force_password_rotation' => true,
            )
        ));
        $pm->encodeAndSetPassword($user, 'foo');

        $meta = $user->getMeta();
        $this->assertArrayHasKey('modera_security', $meta);
        $this->assertArrayHasKey('used_passwords', $meta['modera_security']);
        $this->assertEquals(1, count($meta['modera_security']['used_passwords']));
        $this->assertLessThan(10, array_keys($meta['modera_security']['used_passwords'])[0] - time());
        $usedPasswords = array_values($meta['modera_security']['used_passwords']);
        $this->assertEquals('encoded-foo', $usedPasswords[0]);
        $this->assertArrayNotHasKey('force_password_rotation', $meta['modera_security']);
    }

    public function testEncodeAndSetPassword_rotationCheckFail()
    {
        $passwordConfigMock = \Phake::mock(PasswordConfigInterface::class);
        \Phake::when($passwordConfigMock)
            ->getRotationPeriodInDays()
            ->thenReturn(99)
        ;

        $encoderDummy = new UserPasswordEncoderDummy();
        $encoderDummy->mapping = array(
            'foo' => 'encoded-foo',
            'bar' => 'encoded-bar',
        );
        $validatorMock = \Phake::mock(ValidatorInterface::class);
        \Phake::when($validatorMock)
            ->validate(\Phake::anyParameters())
            ->thenReturn([])
        ;

        $pm = new PasswordManager(
            $passwordConfigMock, $encoderDummy, $validatorMock, \Phake::mock(MailServiceInterface::class)
        );

        $user = new User();
        $user->setMeta(array(
            'modera_security' => array(
                'used_passwords' => array(
                    $this->createTimeWithDaysAgo(130) => 'encoded-bar',
                    $this->createTimeWithDaysAgo(25) => 'encoded-foo',
                ),
            ),
        ));

        $thrownException = null;
        try {
            $pm->encodeAndSetPassword($user, 'foo');
        } catch (BadPasswordException $e) {
            $thrownException = $e;
        }
        $this->assertEquals(
            'Given password cannot be used because it has been already used in last 99 days.',
            $thrownException->getMessage()
        );
        $this->assertEquals(2, count($user->getMeta()['modera_security']['used_passwords']));

        $pm->encodeAndSetPassword($user, 'bar');
        $this->assertEquals(3, count($user->getMeta()['modera_security']['used_passwords']));
    }

    /**
     * @expectedException Modera\SecurityBundle\PasswordStrength\BadPasswordException
     * @expectedExceptionMessage error-msg1
     */
    public function testEncodeAndSetPassword_validationFail()
    {
        $passwordConfigMock = \Phake::mock(PasswordConfigInterface::class);
        \Phake::when($passwordConfigMock)
            ->getRotationPeriodInDays()
            ->thenReturn(false)
        ;

        $encoderDummy = new UserPasswordEncoderDummy();
        $encoderDummy->mapping = array(
            'foo' => 'encoded-foo',
        );

        $violation = \Phake::mock(ConstraintViolation::class);
        \Phake::when($violation)
            ->getMessage()
            ->thenReturn('error-msg1')
        ;

        $validatorMock = \Phake::mock(ValidatorInterface::class);
        \Phake::when($validatorMock)
            ->validate('foo', $this->isInstanceOf(StrongPassword::class))
            ->thenReturn([$violation])
        ;

        $pm = new PasswordManager(
            $passwordConfigMock, $encoderDummy, $validatorMock, \Phake::mock(MailServiceInterface::class)
        );

        $user = new User();
        $pm->encodeAndSetPassword($user, 'foo');
    }

    private function assertGeneratePassword($minLength, $letterRequired)
    {
        $encoderDummy = new UserPasswordEncoderDummy();
        $validatorMock = \Phake::mock(ValidatorInterface::class);
        \Phake::when($validatorMock)
            ->validate(\Phake::anyParameters())
            ->thenReturn([])
        ;

        $pm = new PasswordManager(
            $this->createPasswordConfigMock($minLength, true, $letterRequired),
            $encoderDummy,
            $validatorMock,
            \Phake::mock(MailServiceInterface::class)
        );

        $password = $pm->generatePassword();

        switch ($letterRequired) {
            case PasswordConfigInterface::LETTER_REQUIRED_TYPE_CAPITAL_OR_NON_CAPITAL:
                $pattern = '/[A-Za-z]/';
                break;
            case PasswordConfigInterface::LETTER_REQUIRED_TYPE_CAPITAL_AND_NON_CAPITAL:
                $pattern = '/(?=.*[A-Z])(?=.*[a-z])/';
                break;
            case PasswordConfigInterface::LETTER_REQUIRED_TYPE_CAPITAL:
                $pattern = '/[A-Z]/';
                break;
            case PasswordConfigInterface::LETTER_REQUIRED_TYPE_NON_CAPITAL:
                $pattern = '/[a-z]/';
                break;
        }

        $this->assertNotNull($password);
        $this->assertTrue(strlen($password) == $minLength);
        $this->assertRegExp('/[0-9]/', $password);
        $this->assertRegExp($pattern, $password);
    }

    public function testGeneratePassword()
    {
        $this->assertGeneratePassword(6, PasswordConfigInterface::LETTER_REQUIRED_TYPE_CAPITAL_OR_NON_CAPITAL);
        $this->assertGeneratePassword(8, PasswordConfigInterface::LETTER_REQUIRED_TYPE_CAPITAL_AND_NON_CAPITAL);
        $this->assertGeneratePassword(10, PasswordConfigInterface::LETTER_REQUIRED_TYPE_CAPITAL);
        $this->assertGeneratePassword(12, PasswordConfigInterface::LETTER_REQUIRED_TYPE_NON_CAPITAL);
    }

    public function testEncodeAndSetPasswordAndThenEmailIt()
    {
        $mailServiceMock = \Phake::mock(MailServiceInterface::class);

        $validatorMock = \Phake::mock(ValidatorInterface::class);
        \Phake::when($validatorMock)
            ->validate(\Phake::anyParameters())
            ->thenReturn([])
        ;

        $pm = new PasswordManager(
            \Phake::mock(PasswordConfigInterface::class),
            \Phake::mock(UserPasswordEncoderInterface::class),
            $validatorMock,
            $mailServiceMock
        );

        $user = new User();

        $pm->encodeAndSetPasswordAndThenEmailIt($user, 'foobar');

        $this->assertArrayHasKey('modera_security', $user->getMeta());
        $meta = $user->getMeta()['modera_security'];
        $this->assertArrayHasKey('used_passwords', $meta);
        $this->assertEquals(1, count($meta['used_passwords']));
        $this->assertArrayHasKey('force_password_rotation', $meta);

        \Phake::verify($mailServiceMock)
            ->sendPassword($user, 'foobar')
        ;
    }

    public function testIsItTimeToRotatePassword_aftetItHasBeenEmailed()
    {
        $passwordConfigMock = \Phake::mock(PasswordConfigInterface::class);
        \Phake::when($passwordConfigMock)
            ->getRotationPeriodInDays()
            ->thenReturn(90)
        ;
        $encoderDummy = new UserPasswordEncoderDummy();
        $validatorMock = \Phake::mock(ValidatorInterface::class);
        \Phake::when($validatorMock)
            ->validate(\Phake::anyParameters())
            ->thenReturn([])
        ;

        $pm = new PasswordManager(
            $passwordConfigMock,
            $encoderDummy,
            $validatorMock,
            \Phake::mock(MailServiceInterface::class)
        );

        $user = new User();
        $user->setMeta(
            array(
                'modera_security' => array(
                    'used_passwords' => array(
                        time() => '1234',
                    ),
                    'force_password_rotation' => true,
                )
            )
        );

        $this->assertTrue($pm->isItTimeToRotatePassword($user));

        $user->setMeta(array(
            'modera_security' => array(
                'used_passwords' => array(
                    time() => '1234',
                ),
            )
        ));

        $this->assertFalse($pm->isItTimeToRotatePassword($user));
    }

    private function createPasswordConfigMock($minLength, $isNumberRequired, $letterRequired)
    {
        $pc = \Phake::mock(PasswordConfigInterface::class);
        \Phake::when($pc)
            ->getMinLength()
            ->thenReturn($minLength)
        ;
        \Phake::when($pc)
            ->isNumberRequired()
            ->thenReturn($isNumberRequired)
        ;
        \Phake::when($pc)
            ->isLetterRequired()
            ->thenReturn(false !== $letterRequired)
        ;
        \Phake::when($pc)
            ->getLetterRequiredType()
            ->thenReturn($letterRequired)
        ;
        \Phake::when($pc)
            ->getRotationPeriodInDays()
            ->thenReturn(90)
        ;

        return $pc;
    }

    private function createTimeWithDaysAgo($days)
    {
        $now = new \DateTime('now');
        $now->modify("-$days day");

        return $now->getTimestamp();
    }
}