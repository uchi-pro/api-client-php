<?php

declare(strict_types=1);

namespace UchiPro\Leads;

class Comment
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $text;

    /**
     * @param string|null $id
     * @param string|null $text
     *
     * @return Comment
     */
    public static function create(string $id = null, string $text = null): Comment
    {
        $comment = new self();
        $comment->id = $id;
        $comment->text = $text;
        return $comment;
    }
}
