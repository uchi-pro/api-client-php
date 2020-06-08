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
     * @var string
     */
    public $gid;

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
     * @var AcademicPlan\Plan
     */
    public $academicPlan;

    /**
     * @param string $id
     * @param string $title
     *
     * @return Course
     */
    public static function create($id, $title)
    {
        $course = new self();
        $course->id = $id;
        $course->title = $title;
        return $course;
    }
}
