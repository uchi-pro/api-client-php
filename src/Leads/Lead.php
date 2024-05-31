<?php

declare(strict_types=1);

namespace UchiPro\Leads;

use UchiPro\Courses\Course;
use UchiPro\Users\User;

class Lead
{
    public ?string $id = null;

    public ?User $contractor = null;

    public ?string $number = null;

    public ?string $contactPerson = null;

    public ?string $email = null;

    public ?string $phone = null;

    /**
     * @var Course[]
     */
    public ?array $courses = null;
}
