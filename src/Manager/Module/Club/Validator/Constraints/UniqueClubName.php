<?php

declare(strict_types=1);

namespace App\Manager\Module\Club\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class UniqueClubName extends Constraint
{
    public string $message = 'wizard.club.errors.name_already_exists';

    public function getTargets(): string|array
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
