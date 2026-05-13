<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
class NoProfanity extends Constraint
{
    public string $message = 'A szöveg nem megfelelő kifejezést tartalmaz.';
}
