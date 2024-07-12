<?php

namespace Modera\SecurityBundle\PasswordStrength;

use Symfony\Component\Validator\Constraint;

/**
 * Use PasswordManager::validatePassword() instead for now. This annotation has been added as a part
 * of an initial design of the password-strength package, but it is clear now that the design could
 * have been simplified. If you need to use this annotation, let me know, otherwise after some
 * time if nobody still needs to use it, it will be removed.
 *
 * @internal
 *
 * @Annotation
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2017 Modera Foundation
 */
class StrongPassword extends Constraint
{
}
