<?php

namespace UchiPro\Courses;

use UchiPro\Vendors\Vendor;

class Query
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
