<?php

declare(strict_types=1);

namespace UchiPro\Sessions;

class Status
{
    public const STATUS_STARTED = 'started';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    public ?string $code = null;

    public ?string $title = null;

    public function isStarted(): bool
    {
        return $this->code === self::STATUS_STARTED;
    }

    public function isCompleted(): bool
    {
        return $this->code === self::STATUS_COMPLETED;
    }

    public function isAccepted(): bool
    {
        return $this->code === self::STATUS_ACCEPTED;
    }

    public function isRejected(): bool
    {
        return $this->code === self::STATUS_REJECTED;
    }

    public static function create(?string $code = null, ?string $title = null): self
    {
        $status = new self();
        $status->code = $code;
        $status->title = $title;
        return $status;
    }

    public static function createStarted(): self
    {
        return self::create(self::STATUS_STARTED, 'Идёт обучение');
    }

    public static function createCompleted(): self
    {
        return self::create(self::STATUS_COMPLETED, 'На проверке');
    }

    public static function createAccepted(): self
    {
        return self::create(self::STATUS_ACCEPTED, 'Выполнено успешно');
    }

    public static function createRejected(): self
    {
        return self::create(self::STATUS_REJECTED, 'Выполнено неверно');
    }
}
