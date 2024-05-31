<?php

declare(strict_types=1);

namespace UchiPro\Orders;

use DateTimeImmutable;
use UchiPro\Courses\Course;
use UchiPro\Users\User;
use UchiPro\Vendors\Vendor;

class Order
{
    public ?string $id = null;

    public ?DateTimeImmutable $createdAt = null;

    public ?DateTimeImmutable $updatedAt = null;

    public ?DateTimeImmutable $deletedAt = null;

    public ?string $number = null;

    public ?Status $status = null;

    public ?Course $course = null;

    public ?Vendor $vendor = null;

    public ?User $contractor = null;

    /**
     * @var null|array|User[]
     */
    public ?array $listeners = null;

    public ?int $listenersCount = null;

    public ?int $listenersFinished = null;

    public ?DateTimeImmutable $sessionStartsAt = null;

    public ?DateTimeImmutable $sessionEndsAt = null;

    public static function create(?string $id = null, ?string $number = null): self
    {
        $order = new self();
        $order->id = $id;
        $order->number = $number;
        return $order;
    }
}
