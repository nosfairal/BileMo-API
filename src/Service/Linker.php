<?php

namespace App\Service;

class Linker
{
    public function __construct($items, $total)
    {
        $this->total = $total;
        $this->items = $items;
        $this->count = count($items);
    }

    public function addLink($ref, $url)
    {
        $this->_links[$ref] = $url;
    }
}