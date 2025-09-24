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

    /**
     * Доступны опросы по заявкам
     *
     * @var bool
     */
    public $surveysAvailable;

    /**
     * Разрешено использовать мобильное приложение?
     *
     * @var bool
     */
    public $mobileAppEnabled;

    /**
     * Доступен раздел новостей
     *
     * @var bool
     */
    public $newsAvailable;

    /**
     * Доступна синхронизация данных между СДО
     *
     * @var bool
     */
    public $syncEnabled;

    /**
     * Доступен базовый учебный документооборот
     *
     * @var bool
     */
    public $protocolsAvailable;

    /**
     * Доступен раздел документов с простой электронной подписью (ПЭП)
     *
     * @var bool
     */
    public $signedFilesAvailable;

    /**
     * Создание собственных курсов обучения запрещено
     *
     * @var bool
     */
    public $customCoursesDisabled;

    public static function create(): self
    {
        return new self();
    }
}
