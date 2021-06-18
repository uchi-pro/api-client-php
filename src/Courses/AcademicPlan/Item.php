<?php

declare(strict_types=1);

namespace UchiPro\Courses\AcademicPlan;

class Item
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var ItemType
     */
    public $type;

    /**
     * @var int|string
     */
    public $hours;
}
