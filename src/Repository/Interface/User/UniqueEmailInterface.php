<?php

namespace App\Repository\Interface\User;

interface UniqueEmailInterface
{
    public function isEmailAddressUnique(string $email): bool;
}
