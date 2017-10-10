<?php

namespace Modera\SecurityBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @internal
 *
 * @Annotation
 *
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2017 Modera Foundation
 */
class Username extends Constraint
{
    public $service = 'modera_security.validator.username';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return $this->service;
    }
}
