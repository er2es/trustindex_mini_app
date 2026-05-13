<?php

declare(strict_types=1);

namespace App\Validator;

use App\Service\ProfanityFilter;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NoProfanityValidator extends ConstraintValidator
{
    public function __construct(private readonly ProfanityFilter $profanityFilter)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof NoProfanity) {
            throw new UnexpectedTypeException($constraint, NoProfanity::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if ($this->profanityFilter->containsProfanity((string) $value)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
