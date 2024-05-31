<?php

declare(strict_types=1);

namespace UchiPro\Users;

class Role
{
    public ?string $id = null;

    public ?string $title = null;

    public static function create(string $id = null, string $title = null): Role
    {
        $role = new self();
        $role->id = $id;
        $role->title = $title;
        return $role;
    }

    public static function createAdministrator(): Role
    {
        return self::create('administrator', 'Администратор');
    }

    public static function createManager(): Role
    {
        return self::create('manager', 'Менеджер');
    }

    public static function createEditor(): Role
    {
        return self::create('editor', 'Редактор');
    }

    public static function createTeacher(): Role
    {
        return self::create('teacher', 'Преподаватель');
    }

    public static function createAgent(): Role
    {
        return self::create('agent', 'Агент');
    }

    public static function createContractor(): Role
    {
        return self::create('contractor', 'Контрагент');
    }

    public static function createListener(): Role
    {
        return self::create('listener', 'Слушатель');
    }

    public static function createGuest(): Role
    {
        return self::create('guest', 'Гость');
    }
}
