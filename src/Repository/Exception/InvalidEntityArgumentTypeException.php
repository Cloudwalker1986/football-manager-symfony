<?php

declare(strict_types=1);

namespace App\Repository\Exception;

final class InvalidEntityArgumentTypeException extends \InvalidArgumentException
{
    public function __construct(string $expectedType, string $givenType)
    {
        parent::__construct(
            sprintf(
                'Invalid argument type for $entity. Expected "%s", got "%s".',
                $expectedType,
                $givenType
            )
        );
    }
}
