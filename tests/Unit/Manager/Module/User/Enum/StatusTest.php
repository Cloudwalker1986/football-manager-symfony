<?php

declare(strict_types=1);

namespace App\UnitTests\Manager\Module\User\Enum;

use App\Manager\Module\User\Enum\Status;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversMethod(Status::class, 'isNull')]
#[CoversMethod(Status::class, 'isVerified')]
#[CoversMethod(Status::class, 'isDeleted')]
#[CoversMethod(Status::class, 'isNotVerified')]
#[Group('unit-tests')]
class StatusTest extends TestCase
{
    #[dataProvider('dataProviderIsNull')]
    #[Test]
    public function isStatusNull(Status $current, bool $expected): void
    {
        self::assertSame($expected, $current->isNull());
    }

    #[dataProvider('dataProviderIsVerified')]
    #[Test]
    public function isVerified(Status $current, bool $expected): void
    {
        self::assertSame($expected, $current->isVerified());
    }

    #[dataProvider('dataProviderIsNotVerified')]
    #[Test]
    public function isNotVerified(Status $current, bool $expected): void
    {
        self::assertSame($expected, $current->isNotVerified());
    }

    #[dataProvider('dataProviderIsDeleted')]
    #[Test]
    public function isDeleted(Status $current, bool $expected): void
    {
        self::assertSame($expected, $current->isDeleted());
    }

    public static function dataProviderIsNull(): array
    {
        return [
            'status is not null 1' => [
                'current' => Status::DELETED,
                'expected' => false
            ],
            'status is not null 2' => [
                'current' => Status::NOT_VERIFIED,
                'expected' => false
            ],
            'status is not null 3' => [
                'current' => Status::VERIFIED,
                'expected' => false
            ],
            'status is null' => [
                'current' => Status::NULL,
                'expected' => true
            ]
        ];
    }

    public static function dataProviderIsVerified(): array
    {
        return [
            'status is not null 1' => [
                'current' => Status::DELETED,
                'expected' => false
            ],
            'status is not null 2' => [
                'current' => Status::NOT_VERIFIED,
                'expected' => false
            ],
            'status is not null 3' => [
                'current' => Status::VERIFIED,
                'expected' => true
            ],
            'status is null' => [
                'current' => Status::NULL,
                'expected' => false
            ]
        ];
    }

    public static function dataProviderIsNotVerified(): array
    {
        return [
            'status is not null 1' => [
                'current' => Status::DELETED,
                'expected' => false
            ],
            'status is not null 2' => [
                'current' => Status::NOT_VERIFIED,
                'expected' => true
            ],
            'status is not null 3' => [
                'current' => Status::VERIFIED,
                'expected' => false
            ],
            'status is null' => [
                'current' => Status::NULL,
                'expected' => false
            ]
        ];
    }

    public static function dataProviderIsDeleted(): array
    {
        return [
            'status is not null 1' => [
                'current' => Status::DELETED,
                'expected' => true
            ],
            'status is not null 2' => [
                'current' => Status::NOT_VERIFIED,
                'expected' => false
            ],
            'status is not null 3' => [
                'current' => Status::VERIFIED,
                'expected' => false
            ],
            'status is null' => [
                'current' => Status::NULL,
                'expected' => false
            ]
        ];

    }
}
