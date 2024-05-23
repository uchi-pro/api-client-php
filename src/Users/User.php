<?php

declare(strict_types=1);

namespace UchiPro\Users;

use UchiPro\Vendors\Vendor;

class User
{
    /**
     * @var ?string
     */
    public $id;

    /**
     * @var ?string
     */
    public $username;

    /**
     * @var ?string
     */
    public $name;

    /**
     * @var ?string
     */
    public $email;

    /**
     * @var ?string
     */
    public $phone;

    /**
     * @var ?Role
     */
    public $role;

    /**
     * @var ?Vendor
     */
    public $vendor;

    /**
     * @var ?bool
     */
    public $isDeleted;

    /**
     * @var ?User
     */
    public $parent;

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
