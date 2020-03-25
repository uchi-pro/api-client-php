<?php

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
     * @param string $id
     * @param string $text
     *
     * @return Comment
     */
    public static function create($id, $text)
    {
        $comment = new self();
        $comment->id = $id;
        $comment->text = $text;
        return $comment;
    }
}