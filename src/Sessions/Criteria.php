<?php

declare(strict_types=1);

namespace UchiPro\Sessions;

use UchiPro\Orders\Order;
use UchiPro\Vendors\Vendor;

class Criteria
{
    /**
     * @var Status|Status[]
     */
    public $status;

    /**
     * @var Order
     */
    public $order;

    /**
     * @var Vendor
     */
    public $vendor;
}
