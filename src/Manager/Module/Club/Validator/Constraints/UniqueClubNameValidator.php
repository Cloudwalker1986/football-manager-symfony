<?php

declare(strict_types=1);

namespace App\Manager\Module\Club\Validator\Constraints;

use App\Repository\ClubRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueClubNameValidator extends ConstraintValidator
{
    public function __construct(
        private readonly ClubRepository $clubRepository
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueClubName) {
            throw new UnexpectedTypeException($constraint, UniqueClubName::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        $existing = $this->clubRepository->findOneBy(['name' => $value]);
        if ($existing) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
