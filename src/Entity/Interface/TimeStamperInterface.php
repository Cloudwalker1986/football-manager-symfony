<?php

namespace App\Entity\Interface;

interface TimeStamperInterface
{
    public function onPrePersistForCreatedAt(): static;

    public function getCreatedAt(): ?\DateTimeImmutable;

    public function onPreUpdateForUpdatedAt(): static;

    public function getUpdatedAt(): ?\DateTimeImmutable;
}
