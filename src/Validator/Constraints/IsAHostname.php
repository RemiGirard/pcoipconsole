<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsAHostname extends Constraint
{
    public $message = 'The string "{{ string }}" contains an illegal character: it can only contain letters, numbers, - or .';
}
