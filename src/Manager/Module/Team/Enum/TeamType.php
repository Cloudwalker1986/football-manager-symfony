<?php

declare(strict_types=1);

namespace App\Manager\Module\Team\Enum;

enum TeamType: string
{
    case FIRST_TEAM = 'FIRST_TEAM';
    case SECOND_TEAM = 'SECOND_TEAM';
    case YOUTH_TEAM = 'YOUTH_TEAM';

    public function isYouthTeam(): bool
    {
        return $this === self::YOUTH_TEAM;
    }

    public function isFirstTeam(): bool
    {
        return $this === self::FIRST_TEAM;
    }

    public function isSecondTeam(): bool
    {
        return $this === self::SECOND_TEAM;
    }
}
