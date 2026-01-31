<?php

declare(strict_types=1);

namespace App\IntegrationTests\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractRepositoryTestCase extends KernelTestCase
{
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get('doctrine')->getManager();
        $this->cleanupDatabase();
    }

    protected function tearDown(): void
    {
        $this->cleanupDatabase();
        $this->entityManager->close();
        parent::tearDown();
    }

    private function cleanupDatabase(): void
    {
        $connection = $this->entityManager->getConnection();
        $databasePlatform = $connection->getDatabasePlatform();

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');

        $tables = ['user_verification', 'manager', 'user'];
        foreach ($tables as $table) {
            $connection->executeStatement(sprintf('DELETE FROM %s', $table));
        }

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }
}
