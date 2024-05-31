<?php

declare(strict_types=1);

namespace UchiPro\Sessions;

use UchiPro\Orders\Order;
use UchiPro\Vendors\Vendor;

class Criteria
{
    /**
     * @var Status[]|Status|null
     */
    public array|Status|null $status = null;

    public ?Order $order = null;

    public ?Vendor $vendor = null;
}
