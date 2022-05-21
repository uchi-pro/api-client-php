<?php

declare(strict_types=1);

namespace UchiPro\Orders;

use DateTime;
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
     * @var DateTime
     */
    public $createAt;

    /**
     * @var DateTime
     */
    public $updatedAt;

    /**
     * @var DateTime
     */
    public $deletedAt;

    /**
     * @var string
     */
    public $number;

    /**
     * @var Status
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
     * @var array|User[]
     */
    public $listeners;

    /**
     * @var int
     */
    public $listenersCount;

    /**
     * @var int
     */
    public $listenersFinished;
}
