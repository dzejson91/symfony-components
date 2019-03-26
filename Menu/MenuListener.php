<?php
/**
 * Created by PhpStorm.
 * User: Krystian
 * Date: 18.10.2018
 * Time: 20:25
 */

namespace JasonMx\Components\Menu;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class MenuListener implements ContainerAwareInterface
{
    /** @var MenuService */
    protected $menuService;

    /** @var ContainerInterface */
    protected $container;

    /**
     * MenuListener constructor.
     *
     * @param MenuService $menuService
     */
    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param FilterControllerEvent $event
     * @throws \ReflectionException
     */
    public function onKernelController(FilterControllerEvent $event){
        $activeRouteName = $event->getRequest()->attributes->get('_route');
        $this->menuService->setActiveRouteName($activeRouteName);
    }
}