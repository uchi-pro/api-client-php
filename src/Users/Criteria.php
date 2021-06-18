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
}
