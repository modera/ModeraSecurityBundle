<?php

namespace Modera\SecurityBundle\Tests\Unit\PasswordStrength;

use Modera\SecurityBundle\PasswordStrength\PasswordConfigInterface;
use Modera\SecurityBundle\PasswordStrength\PasswordGenerator;

/**
 * @since 2.56.0
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class PasswordGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGeneratePassword()
    {
        $pg = new PasswordGenerator($this->createPasswordConfigMock(8, true, true));
        $password = $pg->generatePassword();
        $this->assertNotNull($password);
        $this->assertTrue(strlen($password) == 8);
        $this->assertRegExp('/[A-Z]/', $password);
        $this->assertRegExp('/[0-9]/', $password);

        $pg = new PasswordGenerator($this->createPasswordConfigMock(12, true, true));
        $password = $pg->generatePassword();
        $this->assertTrue(strlen($password) == 12);
        $this->assertRegExp('/[A-Z]/', $password);
        $this->assertRegExp('/[0-9]/', $password);
    }

    private function createPasswordConfigMock($minLength, $isNumberRequired, $isCapitalLetterRequired)
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
            ->isCapitalLetterRequired()
            ->thenReturn($isCapitalLetterRequired)
        ;

        return $pc;
    }
}