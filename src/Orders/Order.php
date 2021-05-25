<?php

namespace UchiPro\Orders;

use UchiPro\Courses\Course;
use UchiPro\Users\User;
use UchiPro\Vendors\Vendor;

class Order
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $number;

    /**
     * @var string
     */
    public $status;

    /**
     * @var Course
     */
    public $course;

    /**
     * @var Vendor
     */
    public $vendor;

    /**
     * @var User
     */
    public $contractor;

    /**
     * @var int
     */
    public $listenersCount;

    /**
     * @var int
     */
    public $listenersFinished;
}
