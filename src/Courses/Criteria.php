<?php

declare(strict_types=1);

namespace UchiPro\Courses;

use UchiPro\Vendors\Vendor;

class Criteria
{
    public ?Vendor $vendor = null;

    public ?Course $parent = null;

    public ?string $gid = null;

    public ?bool $withInactive = null;

    public ?bool $withDeleted = null;

    /**
     * @var Tag[]
     */
    public ?array $tags = null;

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
