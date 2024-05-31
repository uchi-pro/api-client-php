<?php

declare(strict_types=1);

namespace UchiPro\Courses;

class Tag
{
    public ?string $id = null;

    public ?bool $isActive = null;

    public ?string $parentId = null;

    public ?string $title = null;

    /**
     * @var Tag[]
     */
    public array $children = [];

    public static function create(?string $id = null, ?string $title = null): self
    {
        $tag = new self();
        $tag->id = $id;
        $tag->title = $title;
        return $tag;
    }
}
