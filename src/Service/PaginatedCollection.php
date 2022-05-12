<?php

namespace App\Service;

use Symfony\Component\Serializer\Annotation as Serializer;

class PaginatedCollection
{
    /**
     * @Serializer\Groups({"products:list", "users_list"})
     */
    private $items;
    /**
     * @Serializer\Groups({"products:list", "users_list"})
     */
    private $total;
    /**
     * @Serializer\Groups({"products:list", "users_list"})
     */
    private $count;
    /**
     * @Serializer\Groups({"products:list", "users_list"})
     */
    private $page;
    /**
     * @Serializer\Groups({"products:list", "users_list"})
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