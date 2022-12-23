<?php

declare(strict_types=1);

namespace UchiPro\Vendors;

class Limits
{
    /**
     * Максимальный общий размер всех файлов.
     *
     * @var int
     */
    public $maxTotalFilesize;

    /**
     * Доступна фиксация обучения.
     *
     * @var bool
     */
    public $sessionCheatAvailable;

    /**
     * Вебинары и подобные мероприятия.
     *
     * @var bool
     */
    public $meetingsAvailable;

    /**
     * Лиды заявок и календарь событий по лидам.
     *
     * @var bool
     */
    public $leadsEventsAvailable;

    /**
     * Финансовый документооборот (договоры, счета/оплаты, акты выполненных работ).
     *
     * @var bool
     */
    public $billingDocsAvailable;

    /**
     * Группы по курсу, приказы, выписки, учебные планы.
     *
     * @var bool
     */
    public $groupsWritsAvailable;

    /**
     * База знаний.
     *
     * @var bool
     */
    public $infobaseAvailable;

    /**
     * Онлайн-заказ обучения.
     *
     * @var bool
     */
    public $shopAvailable;

    /**
     * Доступен конструктор курсов по тегам
     *
     * @var bool
     */
    public $courseCompilerAvailable;

    /**
     * Доступны расписания по заявкам, группам по курсу
     *
     * @var bool
     */
    public $schedulesAvailable;
}
