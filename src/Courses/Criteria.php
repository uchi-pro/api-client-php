<?php

namespace UchiPro\Courses;

use UchiPro\Vendors\Vendor;

class Criteria
{
    /**
     * @var Vendor
     */
    public $vendor;

    /**
     * @var bool
     */
    public $withLessons = false;

    /**
     * @var Course
     */
    public $parent;
}
