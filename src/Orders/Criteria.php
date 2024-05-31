<?php

declare(strict_types=1);

namespace UchiPro\Orders;

use DateTimeInterface;
use UchiPro\Vendors\Vendor;

class Criteria
{
    public ?string $number = null;

    public array|null|Status $status = null;

    public ?Vendor $vendor = null;

    public ?bool $withFullAcceptedOnly = null;

    public ?int $page = null;

    public ?int $perPage = null;

    public ?DateTimeInterface $updatedSince = null;
}
