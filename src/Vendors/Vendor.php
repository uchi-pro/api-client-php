<?php

declare(strict_types=1);

namespace UchiPro\Vendors;

use DateTimeImmutable;

class Vendor
{
    public ?string $id = null;

    public ?DateTimeImmutable $createdAt = null;

    public ?bool $isActive = null;

    public ?string $title = null;

    public ?string $email = null;

    /**
     * @var string[]
     */
    public array $domains = [];

    public Company|Person|null $profile;

    public ?Bank $bank;

    public ?Settings $settings;

    public ?Vendor $parent;

    public static function create(?string $id = null, ?string $title = null): self
    {
        $vendor = new self();
        $vendor->id = $id;
        $vendor->title = $title;
        return $vendor;
    }
}
