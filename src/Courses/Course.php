<?php

namespace UchiPro\Courses;

class Course
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $parentId;

    /**
     * @var int
     */
    public $hours;

    /**
     * @var int
     */
    public $price;

    /**
     * @var int
     */
    public $depth = 0;

    /**
     * @var int
     */
    public $childrenCount = 0;

    /**
     * @var int
     */
    public $lessonsCount = 0;
}
