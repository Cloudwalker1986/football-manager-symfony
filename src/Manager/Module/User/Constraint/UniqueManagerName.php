<?php

declare(strict_types=1);

namespace App\Manager\Module\User\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class UniqueManagerName extends Constraint
{
    public string $message = 'registration.manager_name.unique';
}
