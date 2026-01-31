<?php

declare(strict_types=1);

namespace App\UnitTests\Manager\Module\User\Constraint;

use App\Manager\Module\User\Constraint\UniqueManagerName;
use App\Manager\Module\User\Constraint\UniqueManagerNameValidator;
use App\Repository\Interface\Manager\UniqueManagerNameInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

#[CoversClass(UniqueManagerNameValidator::class)]
#[Group('unit-tests')]
class UniqueManagerNameValidatorTest extends ConstraintValidatorTestCase
{
    private UniqueManagerNameInterface&MockObject $repository;

    protected function createValidator(): UniqueManagerNameValidator
    {
        $this->repository = $this->createMock(UniqueManagerNameInterface::class);

        return new UniqueManagerNameValidator($this->repository);
    }

    #[Test]
    #[AllowMockObjectsWithoutExpectations]
    public function itThrowsExceptionIfConstraintIsNotUniqueManagerName(): void
    {
        self::expectException(UnexpectedTypeException::class);

        $constraint = $this->createStub(Constraint::class);
        $this->validator->validate('JohnDoe', $constraint);
    }

    #[Test]
    #[AllowMockObjectsWithoutExpectations]
    public function itDoesNotAddViolationIfValueIsNull(): void
    {
        $this->validator->validate(null, new UniqueManagerName());

        $this->assertNoViolation();
    }

    #[Test]
    #[AllowMockObjectsWithoutExpectations]
    public function itDoesNotAddViolationIfValueIsEmptyString(): void
    {
        $this->validator->validate('', new UniqueManagerName());

        $this->assertNoViolation();
    }

    #[Test]
    #[AllowMockObjectsWithoutExpectations]
    public function itThrowsExceptionIfValueIsNotString(): void
    {
        self::expectException(UnexpectedValueException::class);

        $this->validator->validate(123, new UniqueManagerName());
    }

    #[Test]
    public function itDoesNotAddViolationIfManagerNameIsUnique(): void
    {
        $name = 'JohnDoe';
        $this->repository->expects($this->once())
            ->method('isManagerNameUnique')
            ->with($name)
            ->willReturn(true);

        $this->validator->validate($name, new UniqueManagerName());

        $this->assertNoViolation();
    }

    #[Test]
    public function itAddsViolationIfManagerNameIsNotUnique(): void
    {
        $name = 'TakenName';
        $constraint = new UniqueManagerName();

        $this->repository->expects($this->once())
            ->method('isManagerNameUnique')
            ->with($name)
            ->willReturn(false);

        $this->validator->validate($name, $constraint);

        $this->buildViolation('registration.manager_name.unique')
            ->setParameter('{{ value }}', $name)
            ->assertRaised();
    }
}
