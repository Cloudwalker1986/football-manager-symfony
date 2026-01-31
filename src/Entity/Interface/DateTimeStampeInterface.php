<?php

namespace App\Entity\Interface;

interface DateTimeStampeInterface
{
    public function onPrePersistForCreatedAt(): static;

    public function getCreatedAt(): ?\DateTimeImmutable;

    public function onPreUpdateForUpdatedAt(): static;

    public function getUpdatedAt(): ?\DateTimeImmutable;
}
