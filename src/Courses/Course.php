<?php

declare(strict_types=1);

namespace UchiPro\Courses;

use DateTimeImmutable;
use UchiPro\Users\User;
use UchiPro\Vendors\Vendor;

class Course
{
    public ?string $id = null;

    public ?string $gid = null;

    public ?DateTimeImmutable $createdAt = null;

    public ?DateTimeImmutable $updatedAt = null;

    public ?DateTimeImmutable $deletedAt = null;

    public ?bool $isActive = null;

    public ?User $author = null;

    public ?string $title = null;

    public ?string $description = null;

    public ?string $comments = null;

    public ?string $parentId = null;

    public ?CourseType $type = null;

    public ?int $hours = null;

    public int|float|null $price = null;

    public int $depth = 0;

    public int $childrenCount = 0;

    public int $lessonsCount = 0;

    public ?AcademicPlan\Plan $academicPlan = null;

    public ?Vendor $vendor = null;

    /**
     * @var Tag[]
     */
    public ?array $tags = null;

    public static function create(?string $id = null, ?string $title = null): Course
    {
        $course = new self();
        $course->id = $id;
        $course->title = $title;
        return $course;
    }
}
