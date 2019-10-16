<?php

namespace UchiPro\Courses;

use DateTimeImmutable;

class Course
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var DateTimeImmutable
     */
    public $createdAt;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $parentId;

    /**
     * @var CourseType
     */
    public $type;

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

    /**
     * @var array|Lesson[]
     */
    public $lessons = [];
}
