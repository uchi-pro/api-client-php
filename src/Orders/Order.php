<?php

declare(strict_types=1);

namespace UchiPro\Orders;

use DateTimeImmutable;
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
     * @deprecated
     * @see $createdAt
     * @var DateTimeImmutable
     */
    public $createAt;

    /**
     * @var DateTimeImmutable
     */
    public $createdAt;

    /**
     * @var DateTimeImmutable
     */
    public $updatedAt;

    /**
     * @var DateTimeImmutable
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

    /**
     * @var DateTimeImmutable
     */
    public $sessionStartsAt;

    /**
     * @var DateTimeImmutable
     */
    public $sessionEndsAt;
}
