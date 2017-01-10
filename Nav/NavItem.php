<?php

namespace Doppy\NavBundle\Nav;

class NavItem
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var string|null
     */
    protected $target = null;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var NavItem[]
     */
    protected $children = array();

    /**
     * @var Attributes
     */
    public $attributes;

    /**
     * NavItem constructor.
     *
     * @param string $url
     * @param string $label
     */
    public function __construct($url, $label)
    {
        $this->setLabel($label);
        $this->setUrl($url);
        $this->attributes = new Attributes();
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return null|string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param null|string $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return NavItem[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        return (count($this->children) > 0);
    }

    /**
     * @param NavItem $navItem
     */
    public function addChild(NavItem $navItem)
    {
        $this->children[] = $navItem;
    }
}
