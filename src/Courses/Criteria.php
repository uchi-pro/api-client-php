<?php

declare(strict_types=1);

namespace UchiPro\Courses;

use UchiPro\Vendors\Vendor;

class Criteria
{
    /**
     * @var Vendor
     */
    public $vendor;

    /**
     * @var Course
     */
    public $parent;

    /**
     * @var string
     */
    public $gid;

    /**
     * @var bool
     */
    public $withInactive;

    /**
     * @var bool
     */
    public $withDeleted;

    /**
     * @var Tag[]
     */
    public $tags;

    public function withVendor(Vendor $vendor): self
    {
        $this->vendor = $vendor;
        return $this;
    }

    public function withGid(string $gid): self
    {
        $this->gid = $gid;
        return $this;
    }

    public function withTags(array $tags): self
    {
        $this->tags = $tags;
        return $this;
    }
}
