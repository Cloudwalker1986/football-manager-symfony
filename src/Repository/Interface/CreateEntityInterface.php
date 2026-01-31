<?php

namespace App\Repository\Interface;

interface CreateEntityInterface
{
    public function persist(object $entity): static;

    public function flush(): static;
}
