<?php

declare(strict_types=1);

namespace UchiPro\Sessions;

class Status
{
    const STATUS_STARTED = 'started';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';

    /**
     * @var string
     */
    public $code;

    /**
     * @var string
     */
    public $title;

    public function isStarted()
    {
        return $this->code === self::STATUS_STARTED;
    }

    public function isCompleted()
    {
        return $this->code === self::STATUS_COMPLETED;
    }

    public function isAccepted()
    {
        return $this->code === self::STATUS_ACCEPTED;
    }

    public function isRejected()
    {
        return $this->code === self::STATUS_REJECTED;
    }

    public static function create(string $code = null, string $title = null)
    {
        $status = new self();
        $status->code = $code;
        $status->title = $title;
        return $status;
    }

    public static function createStarted(): Status
    {
        return self::create(self::STATUS_STARTED, 'Идёт обучение');
    }

    public static function createCompleted(): Status
    {
        return self::create(self::STATUS_COMPLETED, 'На проверке');
    }

    public static function createAccepted(): Status
    {
        return self::create(self::STATUS_ACCEPTED, 'Выполнено успешно');
    }

    public static function createRejected(): Status
    {
        return self::create(self::STATUS_REJECTED, 'Выполнено неверно');
    }
}
