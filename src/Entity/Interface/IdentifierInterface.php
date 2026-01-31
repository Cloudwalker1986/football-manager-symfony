<?php

namespace App\Entity\Interface;


use Symfony\Component\Uid\Uuid;

interface IdentifierInterface
{
    public function setId(?int $id): static;

    public function getId(): ?int;

    public function onPrePersistForUuid(): static;

    public function getUuid(): ?Uuid;
}
