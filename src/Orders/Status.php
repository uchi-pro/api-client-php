<?php

declare(strict_types=1);

namespace UchiPro\Orders;

class Status
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
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $code;

    /**
     * @var string
     */
    public $title;


    public function isPending(): bool
    {
        return $this->code === self::STATUS_PENDING;
    }

    public function isAccepted(): bool
    {
        return $this->code === self::STATUS_ACCEPTED;
    }

    public function isAwaitingPayment(): bool
    {
        return $this->code === self::STATUS_AWAITING_PAYMENT;
    }

    public function isTraining(): bool
    {
        return $this->code === self::STATUS_TRAINING;
    }

    public function isTrainingComplete(): bool
    {
        return $this->code === self::STATUS_TRAINING_COMPLETE;
    }

    public function isDocumentsReady(): bool
    {
        return $this->code === self::STATUS_DOCUMENTS_READY;
    }

    public function isCompleted(): bool
    {
        return $this->code === self::STATUS_COMPLETED;
    }

    public function isCanceled(): bool
    {
        return $this->code === self::STATUS_CANCELED;
    }

    public function greaterThan(Status $status): bool
    {
        return $this->id > $status->id;
    }

    private static function create(int $id, string $code, string $title): Status
    {
        $status = new self();
        $status->id = $id;
        $status->code = $code;
        $status->title = $title;
        return $status;
    }

    public static function createPending(): Status
    {
        return self::create(0, self::STATUS_PENDING, 'Не обработана');
    }

    public static function createAccepted(): Status
    {
        return self::create(5, self::STATUS_ACCEPTED, 'Принята в работу');
    }

    public static function createAwaitingPayment(): Status
    {
        return self::create(10, self::STATUS_AWAITING_PAYMENT, 'Ожидание оплаты');
    }

    public static function createTraining(): Status
    {
        return self::create(20, self::STATUS_TRAINING, 'Идет обучение');
    }

    public static function createTrainingComplete(): Status
    {
        return self::create(30, self::STATUS_TRAINING_COMPLETE, 'Обучение завершено');
    }

    public static function createDocumentsReady(): Status
    {
        return self::create(40, self::STATUS_DOCUMENTS_READY, 'Документы готовы');
    }

    public static function createCompleted(): Status
    {
        return self::create(50, self::STATUS_COMPLETED, 'Заявка выполнена');
    }

    public static function createCanceled(): Status
    {
        return self::create(127, self::STATUS_CANCELED, 'Отменена');
    }

    public static function createByCode(string $code): Status
    {
        $statuses = [
            self::STATUS_PENDING => self::createPending(),
            self::STATUS_ACCEPTED => self::createAccepted(),
            self::STATUS_AWAITING_PAYMENT => self::createAwaitingPayment(),
            self::STATUS_TRAINING => self::createTraining(),
            self::STATUS_TRAINING_COMPLETE => self::createTrainingComplete(),
            self::STATUS_DOCUMENTS_READY => self::createDocumentsReady(),
            self::STATUS_COMPLETED => self::createCompleted(),
            self::STATUS_CANCELED => self::createCanceled(),
        ];

        return $statuses[$code];
    }
}
