<?php

namespace UchiPro\Users;

use UchiPro\Vendors\Vendor;

class User
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var Role
     */
    public $role;

    /**
     * @var Vendor
     */
    public $vendor;
}
