<?php

/**
 * @author Krystian Jasnos <dzejson91@gmail.com>
 */

namespace JasonMx\Components\Menu\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Class MenuItem
 * @package JasonMx\Components\Menu\Annotation
 *
 * @Annotation
 */
class MenuItem
{
    /**
     * @var string $title
     */
    public $title;

    /**
     * @var string $path
     */
    public $path;

    /**
     * @var string $path
     */
    public $name;

    /**
     * @var string $path
     */
    public $menu = 'default';

    /**
     * @var string $path
     */
    public $parent = 'default';

    /**
     * @var string $icon
     */
    public $icon;

    /**
     * @var int $order
     */
    public $order = 100;

    /**
     * @var string $role
     */
    public $role;

    /**
     * @return array
     */
    public $items;

    /**
     * @return bool
     */
    public $active;

    /** @var static */
    public $controller;

    /**
     * MenuItem constructor.
     */
    public function __construct(){
        $this->items = array();
    }

    public function __toArray(){
        return json_decode(json_encode($this), true);
    }
}