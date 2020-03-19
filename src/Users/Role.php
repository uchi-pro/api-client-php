<?php

namespace UchiPro\Users;

class Role
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @param $id
     * @param $title
     *
     * @return Role
     */
    public static function create($id, $title)
    {
        $role = new self();
        $role->id = $id;
        $role->title = $title;
        return $role;
    }
}
