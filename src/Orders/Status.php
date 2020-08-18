<?php

namespace UchiPro\Orders;

class Status
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $code;

    /**
     * @var string
     */
    public $title;

    /**
     * @param int $id
     * @param string $code
     * @param string $title
     *
     * @return Status
     */
    public static function create($id, $code, $title)
    {
        $status = new self();
        $status->id = $id;
        $status->code = $code;
        $status->title = $title;
        return $status;
    }
}
