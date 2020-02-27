<?php

namespace UchiPro\Leads;

use UchiPro\Courses\Course;
use UchiPro\Users\User;

class Lead
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var User
     */
    public $contractor;

    /**
     * @var string
     */
    public $number;

    /**
     * @var string
     */
    public $contactPerson;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $phone;

    /**
     * @var Course[]
     */
    public $courses;
}
