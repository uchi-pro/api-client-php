<?php

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
     * @var string
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

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return !empty($this->deletedAt);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return !$this->isDeleted() && !$this->isAccepted();
    }

    /**
     * @return bool
     */
    public function isStarted(): bool
    {
        return $this->status === Criteria::STATUS_STARTED;
    }

    /**
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === Criteria::STATUS_COMPLETED;
    }

    /**
     * @return bool
     */
    public function isAccepted(): bool
    {
        return $this->status === Criteria::STATUS_ACCEPTED;
    }

    /**
     * @return bool
     */
    public function isRejected(): bool
    {
        return $this->status === Criteria::STATUS_REJECTED;
    }
}
