<?php

namespace App\Service;

use Symfony\Component\Serializer\Annotation as Serializer;

class PaginatedCollection
{
    /**
     * @Serializer\Groups({"products:list", "users:list"})
     */
    public $items;
    /**
     * @Serializer\Groups({"products:list", "users:list"})
     */
    public $total;
    /**
     * @Serializer\Groups({"products:list", "users:list"})
     */
    public $count;
    /**
     * @Serializer\Groups({"products:list", "users:list"})
     */
    public $page;
    /**
     * @Serializer\Groups({"products:list", "users:list"})
     */
    public $_links = [];

    public function __construct($items, $total, $page)
    {
        $this->total = $total;
        $this->items = $items;
        $this->page  = $page;
        $this->count = count($items);
    }

    public function addLink($ref, $url)
    {
        $this->_links[$ref] = $url;
    }
}