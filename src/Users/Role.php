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

    /**
     * @return Role
     */
    public static function createAdministrator()
    {
        return self::create('administrator', 'Администратор');
    }

    /**
     * @return Role
     */
    public static function createManager()
    {
        return self::create('manager', 'Менеджер');
    }

    /**
     * @return Role
     */
    public static function createEditor()
    {
        return self::create('editor', 'Редактор');
    }

    /**
     * @return Role
     */
    public static function createTeacher()
    {
        return self::create('teacher', 'Преподаватель');
    }

    /**
     * @return Role
     */
    public static function createAgent()
    {
        return self::create('agent', 'Агент');
    }

    /**
     * @return Role
     */
    public static function createContractor()
    {
        return self::create('contractor', 'Контрагент');
    }

    /**
     * @return Role
     */
    public static function createListener()
    {
        return self::create('listener', 'Слушатель');
    }

    /**
     * @return Role
     */
    public static function createGuest()
    {
        return self::create('guest', 'Гость');
    }
}
