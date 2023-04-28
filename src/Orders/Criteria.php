<?php

declare(strict_types=1);

namespace UchiPro\Orders;

use DateTimeInterface;
use UchiPro\Vendors\Vendor;

class Criteria
{
    /**
     * @var string
     */
    public $number;

    /**
     * @var Status|Status[]
     */
    public $status;

    /**
     * @var Vendor
     */
    public $vendor;

    /**
     * @var bool
     */
    public $withFullAcceptedOnly;

    /**
     * @var ?int
     */
    public $page;

    /**
     * @var ?int
     */
    public $perPage;

    /**
     * @var DateTimeInterface
     */
    public $updatedSince;
}
