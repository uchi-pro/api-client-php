<?php

declare(strict_types=1);

namespace UchiPro\Vendors;

use DateTimeImmutable;

class Vendor
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var DateTimeImmutable
     */
    public $createdAt;

    /**
     * @var bool
     */
    public $isActive;

    /**
     * @var ?string
     */
    public $title;

    /**
     * @var string
     */
    public $email;

    /**
     * @var array|string[]
     */
    public $domains = [];

    /**
     * @var Settings
     */
    public $settings;

    /**
     * @param $id
     * @param $title
     *
     * @return Vendor
     */
    public static function create($id, $title)
    {
        $vendor = new self();
        $vendor->id = $id;
        $vendor->title = $title;
        return $vendor;
    }
}
