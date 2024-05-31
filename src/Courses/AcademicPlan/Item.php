<?php

declare(strict_types=1);

namespace UchiPro\Courses\AcademicPlan;

class Item
{
    public ?string $title;

    public ?ItemType $type;

    public string|int|float|null $hours;
}
