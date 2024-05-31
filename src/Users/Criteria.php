<?php

declare(strict_types=1);

namespace UchiPro\Users;

use UchiPro\Vendors\Vendor;

class Criteria
{
    public ?string $q = null;

    public ?Role $role = null;

    public ?Vendor $vendor = null;

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
