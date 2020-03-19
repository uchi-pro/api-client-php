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
