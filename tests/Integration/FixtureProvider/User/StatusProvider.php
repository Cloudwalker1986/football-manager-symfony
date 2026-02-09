<?php

declare(strict_types=1);

namespace App\IntegrationTests\FixtureProvider\User;

use App\Manager\Module\User\Enum\Status;
use Faker\Provider\Base;

class StatusProvider extends Base
{
    public function statusVerified(): Status
    {
        return Status::VERIFIED;
    }

    public function statusNotVerified(): Status
    {
        return Status::NOT_VERIFIED;
    }
}
