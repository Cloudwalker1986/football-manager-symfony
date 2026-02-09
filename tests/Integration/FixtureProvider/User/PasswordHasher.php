<?php

declare(strict_types=1);

namespace App\IntegrationTests\FixtureProvider\User;

use App\Entity\User;
use App\Manager\Module\User\Enum\Status;
use Faker\Generator;
use Faker\Provider\Base;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordHasher extends Base
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        Generator $generator
    ) {
        parent::__construct($generator);
    }

    public function hashPassword(string $password): string
    {
        return $this->passwordHasher->hashPassword(new User(), $password);
    }
}
