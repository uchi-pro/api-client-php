<?php

namespace UchiPro\Sessions;

use UchiPro\Orders\Order;
use UchiPro\Vendors\Vendor;

class Criteria
{
    const STATUS_STARTED = 'started';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';

    /**
     * @var string|string[]
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
