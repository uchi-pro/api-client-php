<?php

declare(strict_types=1);

namespace UchiPro\Users;

use UchiPro\Vendors\Vendor;

class Criteria
{
    /**
     * @var string
     */
    public $q;

    /**
     * @var Role
     */
    public $role;

    /**
     * @var Vendor
     */
    public $vendor;

    public function withQ(string $q): self
    {
        $this->q = $q;
        return $this;
    }

    public function withRole(Role $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function withVendor(Vendor $vendor): self
    {
        $this->vendor = $vendor;
        return $this;
    }
}
