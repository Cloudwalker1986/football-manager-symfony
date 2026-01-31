<?php

declare(strict_types=1);

namespace App\UnitTests\Manager\Module\User\Constraint;

use App\Manager\Module\User\Constraint\UniqueEmail;
use App\Manager\Module\User\Constraint\UniqueEmailValidator;
use App\Repository\Interface\User\UniqueEmailInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

#[CoversClass(UniqueEmailValidator::class)]
#[Group('unit-tests')]
class UniqueEmailValidatorTest extends ConstraintValidatorTestCase
{
    private UniqueEmailInterface&MockObject $repository;

    protected function createValidator(): UniqueEmailValidator
    {
        $this->repository = $this->createMock(UniqueEmailInterface::class);

        return new UniqueEmailValidator($this->repository);
    }

    #[Test]
    #[AllowMockObjectsWithoutExpectations]
    public function itThrowsExceptionIfConstraintIsNotUniqueEmail(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $constraint = $this->createStub(Constraint::class);
        $this->validator->validate('test@example.com', $constraint);
    }

    #[Test]
    #[AllowMockObjectsWithoutExpectations]
    public function itDoesNotAddViolationIfValueIsNull(): void
    {
        $this->validator->validate(null, new UniqueEmail());

        $this->assertNoViolation();
    }

    #[Test]
    #[AllowMockObjectsWithoutExpectations]
    public function itDoesNotAddViolationIfValueIsEmptyString(): void
    {
        $this->validator->validate('', new UniqueEmail());

        $this->assertNoViolation();
    }

    #[Test]
    #[AllowMockObjectsWithoutExpectations]
    public function itThrowsExceptionIfValueIsNotString(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $this->validator->validate(123, new UniqueEmail());
    }

    #[Test]
    public function itDoesNotAddViolationIfEmailIsUnique(): void
    {
        $email = 'unique@example.com';
        $this->repository->expects($this->once())
            ->method('isEmailAddressUnique')
            ->with($email)
            ->willReturn(true);

        $this->validator->validate($email, new UniqueEmail());

        $this->assertNoViolation();
    }

    #[Test]
    public function itAddsViolationIfEmailIsNotUnique(): void
    {
        $email = 'taken@example.com';
        $constraint = new UniqueEmail();

        $this->repository->expects($this->once())
            ->method('isEmailAddressUnique')
            ->with($email)
            ->willReturn(false);

        $this->validator->validate($email, $constraint);

        $this->buildViolation('registration.email.unique')
            ->setParameter('{{ value }}', $email)
            ->assertRaised();
    }
}
