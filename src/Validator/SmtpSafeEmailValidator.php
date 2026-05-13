<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class SmtpSafeEmailValidator extends ConstraintValidator
{
    // Szigorú regex: nem kezdődhet kötőjellel vagy ponttal, és szigorú a domain része is.
    // mert valójában RFC szerint a -minta.ember@valami.hu valid email cím, de postfix meg lehal miatta
    private const PATTERN = '/^(?![-.])(?=.{1,64}@)[a-zA-Z0-9_+\-]+(?:\.[a-zA-Z0-9_+\-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/u';

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof SmtpSafeEmail) {
            throw new UnexpectedTypeException($constraint, SmtpSafeEmail::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (1 !== preg_match(self::PATTERN, (string) $value)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
