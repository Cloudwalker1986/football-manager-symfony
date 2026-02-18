<?php

declare(strict_types=1);

namespace App\UnitTests\Manager\Module\Team\Enum;

use App\Manager\Module\Team\Enum\TeamType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TeamType::class)]
#[CoversMethod(TeamType::class, 'isFirstTeam')]
#[CoversMethod(TeamType::class, 'isSecondTeam')]
#[CoversMethod(TeamType::class, 'isYouthTeam')]
class TeamTypeTest extends TestCase
{
    #[Test]
    public function itTeamTypeIsFirstTeam(): void
    {
        $type = TeamType::FIRST_TEAM;

        self::assertTrue($type->isFirstTeam());
        self::assertFalse($type->isYouthTeam());
        self::assertFalse($type->isSecondTeam());
    }

    #[Test]
    public function itTeamTypeIsSecondTeam(): void
    {
        $type = TeamType::SECOND_TEAM;

        self::assertFalse($type->isFirstTeam());
        self::assertFalse($type->isYouthTeam());
        self::assertTrue($type->isSecondTeam());
    }

    #[Test]
    public function itTeamTypeIsYouthTeam(): void
    {
        $type = TeamType::YOUTH_TEAM;

        self::assertFalse($type->isFirstTeam());
        self::assertTrue($type->isYouthTeam());
        self::assertFalse($type->isSecondTeam());
    }
}
