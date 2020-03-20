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
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $phone;

    /**
     * @var Role
     */
    public $role;

    /**
     * @var Vendor
     */
    public $vendor;
}
