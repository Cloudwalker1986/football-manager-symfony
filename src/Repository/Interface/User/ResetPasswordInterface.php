<?php

namespace App\Repository\Interface\User;

use App\Repository\Interface\CreateEntityInterface;

interface ResetPasswordInterface extends CreateEntityInterface, RemoveUserTokenInterface
{

}
