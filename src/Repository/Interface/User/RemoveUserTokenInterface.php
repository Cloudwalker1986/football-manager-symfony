<?php

namespace App\Repository\Interface\User;

use App\Entity\User;

interface RemoveUserTokenInterface
{
    public function removeUserToken(User $user): void;
}
