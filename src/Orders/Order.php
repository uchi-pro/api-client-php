<?php

namespace UchiPro\Orders;

use UchiPro\Courses\Course;

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
     * @var int
     */
    public $listenersCount;

    /**
     * @var int
     */
    public $listenersFinished;
}
