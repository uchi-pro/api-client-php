<?php

declare(strict_types=1);

namespace UchiPro\Vendors;

class Criteria
{
    public ?string $q = null;

    public ?bool $isActive = null;

    public function withQ(string $q): self
    {
        $this->q = $q;
        return $this;
    }

    public function withIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }
}
