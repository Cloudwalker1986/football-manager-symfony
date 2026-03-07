<?php

declare(strict_types=1);

namespace App\IntegrationTests\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractRepositoryTestCase extends KernelTestCase
{
    use RefreshDatabaseTrait;

    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        $this->entityManager->close();
        parent::tearDown();
    }
}
