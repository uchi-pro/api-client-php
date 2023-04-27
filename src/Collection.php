<?php

declare(strict_types=1);

namespace UchiPro;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;

class Collection implements ArrayAccess, Countable, IteratorAggregate
{
    private $values = [];

    /**
     * @var ?int
     */
    private $page;

    /**
     * @var ?int
     */
    private $perPage;

    /**
     * @var ?int
     */
    private $totalItems;

    /**
     * @var ?int
     */
    private $totalPages;

    public function __construct(array $values = [])
    {
        $this->values = $values;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->values);
    }

    public function offsetGet($offset)
    {
        return array_key_exists($offset, $this->values) ? $this->values[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->values[] = $value;
        } else {
            $this->values[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->values[$offset]);
    }

    public function count()
    {
        return count($this->values);
    }

    public function setPager(array $pager): void
    {
        $this->page = $pager['page'] ?? null;
        $this->perPage = $pager['items_per_page'] ?? null;
        $this->totalItems = $pager['total_items'] ?? null;
        $this->totalPages = $pager['total_pages'] ?? null;
    }

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function getPerPage(): ?int
    {
        return $this->perPage;
    }

    public function getTotalItems(): ?int
    {
        return $this->totalItems;
    }

    public function getTotalPages(): ?int
    {
        return $this->totalPages;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->values);
    }
}
