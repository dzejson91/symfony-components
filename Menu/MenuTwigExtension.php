<?php
/**
 * Created by PhpStorm.
 * User: Krystian
 * Date: 18.10.2018
 * Time: 20:09
 */

namespace JasonMx\Components\Menu;

class MenuTwigExtension extends \Twig_Extension
{
    /** @var MenuService */
    protected $service;

    /**
     * MenuTwigExtension constructor.
     *
     * @param MenuService $service
     */
    public function __construct(MenuService $service)
    {
        $this->service = $service;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('PanelMenu', array($this->service, 'getMenu')),
        );
    }
}