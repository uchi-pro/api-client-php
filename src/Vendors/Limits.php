<?php

declare(strict_types=1);

namespace UchiPro\Vendors;

class Limits
{
    /**
     * Максимальный общий размер всех файлов.
     */
    public ?int $maxTotalFilesize = null;

    /**
     * Доступна фиксация обучения.
     */
    public ?bool $sessionCheatAvailable = null;

    /**
     * Вебинары и подобные мероприятия.
     */
    public ?bool $meetingsAvailable = null;

    /**
     * Лиды заявок и календарь событий по лидам.
     */
    public ?bool $leadsEventsAvailable = null;

    /**
     * Финансовый документооборот (договоры, счета/оплаты, акты выполненных работ).
     */
    public ?bool $billingDocsAvailable = null;

    /**
     * Группы по курсу, приказы, выписки, учебные планы.
     */
    public ?bool $groupsWritsAvailable = null;

    /**
     * База знаний.
     */
    public ?bool $infobaseAvailable = null;

    /**
     * Онлайн-заказ обучения.
     */
    public ?bool $shopAvailable = null;

    /**
     * Доступен конструктор курсов по тегам
     */
    public ?bool $courseCompilerAvailable = null;

    /**
     * Доступны расписания по заявкам, группам по курсу
     */
    public ?bool $schedulesAvailable = null;

    /**
     * Доступны опросы по заявкам
     */
    public ?bool $surveysAvailable = null;

    /**
     * Разрешено использовать мобильное приложение?
     */
    public ?bool $mobileAppEnabled = null;

    /**
     * Доступен раздел новостей
     */
    public ?bool $newsAvailable = null;

    /**
     * Доступна синхронизация данных между СДО
     */
    public ?bool $syncEnabled = null;

    /**
     * Доступен базовый учебный документооборот
     */
    public ?bool $protocolsAvailable = null;

    public static function create(): self
    {
        return new self();
    }
}
