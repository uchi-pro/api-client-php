<?php

declare(strict_types=1);

namespace UchiPro\Sessions;

use DateTimeImmutable;
use UchiPro\Orders\Order;
use UchiPro\Users\User;

class Session
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var DateTimeImmutable
     */
    public $createdAt;

    /**
     * @var DateTimeImmutable
     */
    public $deletedAt;

    /**
     * @var DateTimeImmutable
     */
    public $startedAt;

    /**
     * @var DateTimeImmutable
     */
    public $skippedAt;

    /**
     * @var DateTimeImmutable
     */
    public $acceptedAt;

    /**
     * @var DateTimeImmutable
     */
    public $completedAt;

    /**
     * @var Status
     */
    public $status;

    /**
     * @var User
     */
    public $listener;

    /**
     * @var Order
     */
    public $order;

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
