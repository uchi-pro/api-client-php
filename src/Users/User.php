<?php

declare(strict_types=1);

namespace UchiPro\Users;

use UchiPro\Vendors\Vendor;

class User
{
    public ?string $id = null;

    public ?string $username = null;

    public ?string $name = null;

    public ?string $email = null;

    public ?string $phone = null;

    public ?Role $role = null;

    public ?Vendor $vendor = null;

    public ?bool $isDeleted = null;

    public ?User $parent = null;

    public static function create(?string $id = null, ?string $name = null): self
    {
        $user = new self();
        $user->id = $id;
        $user->name = $name;
        return $user;
    }

    public static function createAdministrator(?string $id = null, ?string $name = null): self
    {
        $user = self::create($id, $name);
        $user->role = Role::createAdministrator();
        return $user;
    }

    public static function createContractor(?string $id = null, ?string $name = null): self
    {
        $user = self::create($id, $name);
        $user->role = Role::createContractor();
        return $user;
    }

    public static function createListener(?string $id = null, ?string $name = null): self
    {
        $user = self::create($id, $name);
        $user->role = Role::createListener();
        return $user;
    }
}
