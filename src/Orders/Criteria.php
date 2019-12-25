<?php

namespace UchiPro\Orders;

class Criteria
{
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_AWAITING_PAYMENT = 'awaiting_payment';
    const STATUS_TRAINING = 'training';
    const STATUS_TRAINING_COMPLETE = 'training_complete';
    const STATUS_DOCUMENTS_READY = 'documents_ready';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELED = 'canceled';

    /**
     * @var string
     */
    public $number;

    /**
     * @var string|string[]
     */
    public $status;
}
