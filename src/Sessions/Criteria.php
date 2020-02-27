<?php

namespace UchiPro\Sessions;

use UchiPro\Orders\Order;

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
}
