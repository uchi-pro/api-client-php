<?php

declare(strict_types=1);

namespace UchiPro\Sessions;

use DateTimeImmutable;
use UchiPro\Orders\Order;
use UchiPro\Users\User;

class Session
{
    public ?string $id = null;

    public ?DateTimeImmutable $createdAt = null;

    public ?DateTimeImmutable $deletedAt = null;

    public ?DateTimeImmutable $startedAt = null;

    public ?DateTimeImmutable $skippedAt = null;

    public ?DateTimeImmutable $acceptedAt = null;

    public ?DateTimeImmutable $completedAt = null;

    public ?Status $status = null;

    public ?User $listener = null;

    public ?Order $order = null;

    public function isDeleted(): bool
    {
        return !empty($this->deletedAt);
    }

    public function isActive(): bool
    {
        return !$this->isDeleted() && !$this->isAccepted();
    }

    public function isStarted(): bool
    {
        return !is_null($this->status) && $this->status->isStarted();
    }

    public function isCompleted(): bool
    {
        return !is_null($this->status) && $this->status->isCompleted();
    }

    public function isAccepted(): bool
    {
        return !is_null($this->status) && $this->status->isAccepted();
    }

    public function isRejected(): bool
    {
        return !is_null($this->status) && $this->status->isRejected();
    }
}
