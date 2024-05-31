<?php

declare(strict_types=1);

namespace UchiPro\Leads;

class Comment
{
    public ?string $id = null;

    public ?string $text = null;

    /**
     * @param ?string $id
     * @param ?string $text
     *
     * @return Comment
     */
    public static function create(?string $id = null, ?string $text = null): Comment
    {
        $comment = new self();
        $comment->id = $id;
        $comment->text = $text;
        return $comment;
    }
}
