<?php

namespace UchiPro\Vendors;

class Vendor
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var array|string[]
     */
    public $domains = [];

    /**
     * @var Settings
     */
    public $settings;
}
