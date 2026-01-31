<?php

declare(strict_types=1);

namespace App\Manager\Module\User\Constraint;

use App\Repository\Interface\Manager\UniqueManagerNameInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UniqueManagerNameValidator extends ConstraintValidator
{
    public function __construct(
        private readonly UniqueManagerNameInterface $repository
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueManagerName) {
            throw new UnexpectedTypeException($constraint, UniqueManagerName::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (!$this->repository->isManagerNameUnique($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
