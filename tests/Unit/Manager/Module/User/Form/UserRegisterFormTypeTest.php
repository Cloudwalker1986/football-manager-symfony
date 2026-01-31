<?php

declare(strict_types=1);

namespace App\UnitTests\Manager\Module\User\Form;

use App\Manager\Module\User\Command\UserRegistration\UserRegisterCommand;
use App\Manager\Module\User\Constraint\UniqueEmailValidator;
use App\Manager\Module\User\Constraint\UniqueManagerNameValidator;
use App\Manager\Module\User\Form\UserRegisterFormType;
use App\Repository\Interface\Manager\UniqueManagerNameInterface;
use App\Repository\Interface\User\UniqueEmailInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\Validation;

#[CoversClass(UserRegisterFormType::class)]
#[Group('unit-tests')]
#[AllowMockObjectsWithoutExpectations]
class UserRegisterFormTypeTest extends TypeTestCase
{
    private UniqueEmailInterface&MockObject $uniqueEmailInterface;
    private UniqueManagerNameInterface&MockObject $uniqueManagerNameInterface;

    protected function setUp(): void
    {
        error_reporting(E_NOTICE);
        $this->uniqueEmailInterface = $this->createMock(UniqueEmailInterface::class);
        $this->uniqueManagerNameInterface = $this->createMock(UniqueManagerNameInterface::class);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        unset($this->uniqueEmailInterface, $this->uniqueManagerNameInterface);

        parent::tearDown();
    }

    protected function getExtensions(): array
    {
        $validator = Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory(new ConstraintValidatorFactory([
                UniqueEmailValidator::class => new UniqueEmailValidator($this->uniqueEmailInterface),
                UniqueManagerNameValidator::class => new UniqueManagerNameValidator($this->uniqueManagerNameInterface),
            ]))
            ->enableAttributeMapping()
            ->getValidator();

        return [
            new ValidatorExtension($validator),
        ];
    }

    #[Test]
    public function itSubmitsValidData(): void
    {
        $formData = [
            'email' => 'test@example.com',
            'password' => [
                'first' => 'password123',
                'second' => 'password123',
            ],
            'managerName' => 'JohnDoe',
        ];

        $model = new UserRegisterCommand();
        $form = $this->factory->create(UserRegisterFormType::class, $model);

        $expected = new UserRegisterCommand();
        $expected->setEmail('test@example.com');
        $expected->setPassword('password123');
        $expected->setManagerName('JohnDoe');

        $this->uniqueEmailInterface
            ->expects(self::once())
            ->method('isEmailAddressUnique')
            ->willReturn(true);
        $this->uniqueManagerNameInterface
            ->expects(self::once())
            ->method('isManagerNameUnique')
            ->willReturn(true);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isValid());
        self::assertEquals($expected, $model);

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            self::assertArrayHasKey($key, $children);
        }
    }

    #[Test]
    public function itFailsWhenPasswordsDoNotMatch(): void
    {
        $formData = [
            'email' => 'test@example.com',
            'password' => [
                'first' => 'password123',
                'second' => 'different',
            ],
            'managerName' => 'JohnDoe',
        ];

        $model = new UserRegisterCommand();
        $form = $this->factory->create(UserRegisterFormType::class, $model);

        $this->uniqueEmailInterface
            ->expects(self::once())
            ->method('isEmailAddressUnique')
            ->willReturn(true);
        $this->uniqueManagerNameInterface
            ->expects(self::once())
            ->method('isManagerNameUnique')
            ->willReturn(true);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertFalse($form->isValid());

        $errors = iterator_to_array($form->getErrors(true));
        $messages = array_map(fn($e) => $e->getMessage(), $errors);
        self::assertContains('registration.password.mismatch', $messages);
    }

    #[Test]
    public function itFailsWhenEmailIsNotUnique(): void
    {
        $formData = [
            'email' => 'taken@example.com',
            'password' => [
                'first' => 'password123',
                'second' => 'password123',
            ],
            'managerName' => 'JohnDoe',
        ];

        $model = new UserRegisterCommand();
        $form = $this->factory->create(UserRegisterFormType::class, $model);

        $this->uniqueEmailInterface
            ->expects(self::once())
            ->method('isEmailAddressUnique')
            ->with('taken@example.com')
            ->willReturn(false);

        $this->uniqueManagerNameInterface
            ->expects(self::once())
            ->method('isManagerNameUnique')
            ->willReturn(true);

        $form->submit($formData);

        self::assertFalse($form->isValid());
        $errors = iterator_to_array($form->getErrors(true));
        $messages = array_map(fn($e) => $e->getMessage(), $errors);
        self::assertContains('registration.email.unique', $messages);
    }

    #[Test]
    public function itFailsWhenManagerNameIsNotUnique(): void
    {
        $formData = [
            'email' => 'test@example.com',
            'password' => [
                'first' => 'password123',
                'second' => 'password123',
            ],
            'managerName' => 'TakenName',
        ];

        $model = new UserRegisterCommand();
        $form = $this->factory->create(UserRegisterFormType::class, $model);

        $this->uniqueEmailInterface
            ->expects(self::once())
            ->method('isEmailAddressUnique')
            ->willReturn(true);

        $this->uniqueManagerNameInterface
            ->expects(self::once())
            ->method('isManagerNameUnique')
            ->with('TakenName')
            ->willReturn(false);

        $form->submit($formData);

        self::assertFalse($form->isValid());
        $errors = iterator_to_array($form->getErrors(true));
        $messages = array_map(fn($e) => $e->getMessage(), $errors);
        self::assertContains('registration.manager_name.unique', $messages);
    }

    #[Test]
    public function itFailsWhenPasswordIsTooShort(): void
    {
        $formData = [
            'email' => 'test@example.com',
            'password' => [
                'first' => 'short',
                'second' => 'short',
            ],
            'managerName' => 'JohnDoe',
        ];

        $model = new UserRegisterCommand();
        $form = $this->factory->create(UserRegisterFormType::class, $model);

        $this->uniqueEmailInterface
            ->expects(self::once())
            ->method('isEmailAddressUnique')
            ->willReturn(true);

        $this->uniqueManagerNameInterface
            ->expects(self::once())
            ->method('isManagerNameUnique')
            ->willReturn(true);

        $form->submit($formData);

        self::assertFalse($form->isValid());
        $errors = iterator_to_array($form->getErrors(true));
        $messages = array_map(fn($e) => $e->getMessage(), $errors);
        self::assertContains('registration.password.min_length', $messages);
    }
}
