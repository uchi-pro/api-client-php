<?php

declare(strict_types=1);

namespace UchiPro\Courses;

class Tag
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var bool
     */
    public $isActive;

    /**
     * @var string
     */
    public $parentId;

    /**
     * @var string
     */
    public $title;

    /**
     * @var Tag[]
     */
    public $children = [];

    public static function create(?string $id = null, ?string $title = null): self
    {
        $tag = new self();
        $tag->id = $id;
        $tag->title = $title;
        return $tag;
    }
}
