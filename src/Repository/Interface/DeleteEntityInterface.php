<?php

namespace App\Repository\Interface;

interface DeleteEntityInterface
{
    public function delete(object $entity): static;

    public function flush(): static;
}
