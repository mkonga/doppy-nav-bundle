<?php

namespace Doppy\NavBundle\Nav;

class Nav
{
    /**
     * @var NavItem[]
     */
    protected $items = array();

    /**
     * @var Attributes
     */
    public $attributes;

    /**
     * Nav constructor.
     */
    public function __construct()
    {
        $this->attributes = new Attributes();
    }

    /**
     * @return NavItem[]
     */
    public function getChildren()
    {
        return $this->items;
    }

    /**
     * @param NavItem $item
     */
    public function addChild(NavItem $item)
    {
        $this->items[] = $item;
    }

    /**
     * @param NavItem[] $items
     */
    public function addChildren($items)
    {
        foreach ($items as $item) {
            $this->addChild($item);
        }
    }
}
