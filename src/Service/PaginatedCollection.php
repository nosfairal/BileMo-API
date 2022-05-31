<?php

namespace App\Service;

use JMS\Serializer\Annotation as Serializer;

class PaginatedCollection
{
    /**
     * @Serializer\Groups({"products:list", "users:list"})
     */
    private $items;
    /**
     * @Serializer\Groups({"products:list", "users:list"})
     */
    private $total;
    /**
     * @Serializer\Groups({"products:list", "users:list"})
     */
    private $count;
    /**
     * @Serializer\Groups({"products:list", "users:list"})
     */
    private $page;
    /**
     * @Serializer\Groups({"products:list", "users:list"})
     */
    private $_links = [];

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