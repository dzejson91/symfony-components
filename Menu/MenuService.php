<?php
/**
 * Created by PhpStorm.
 * User: Krystian
 * Date: 18.10.2018
 * Time: 17:26
 */

namespace JasonMx\Components\Menu;

use JasonMx\Components\Menu\Annotation\MenuItem;
use JasonMx\Components\Menu\Annotation\MenuParent;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Router;

class MenuService
{
    /** @var ContainerInterface */
    protected $container;

    /** @var array|null  */
    protected $data = null;

    /** @var string */
    protected $activeRouteName;

    /**
     * MenuService constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function setActiveRouteName($activeRouteName){
        $this->activeRouteName = $activeRouteName;
    }

    /**
     * @param $name
     * @return array
     * @throws \Exception
     */
    public function getMenu($name = 'default')
    {
        if(is_null($this->data)){
            $this->prepareMenus();
        }

        if(!array_key_exists($name, $this->data['menus'])){
            throw new \Exception(sprintf('Menu "%s" not found!', $name));
        }
        return $this->data['menus'][$name];
    }

    /**
     * @throws
     */
    protected function prepareMenus(){

        /** @var ArrayAdapter $cacheAdapter */
        $cacheAdapter = $this->container->get('cache.system');
        $cacheItem = $cacheAdapter->getItem('panel_menu_data');

        if(!$cacheItem->isHit()){
            $this->loadMenus();
            $cacheItem->set($this->data);
        } else {
            $this->data = $cacheItem->get();
        }
    }

    /**
     * @throws \ReflectionException
     */
    protected function loadMenus()
    {
        /** @var AnnotationReader $reader */
        $reader = $this->container->get('annotation_reader');

        /** @var Router $router */
        $router = $this->container->get('router');

        $this->data = array(
            'menus' => array(),
            'active' => array(),
        );

        $routes = $router->getRouteCollection();
        foreach($routes as $routeName => $route)
        {
            $controller = $route->getDefault('_controller');
            $controller = explode('::', $controller);
            if(is_null($controller)) continue;
            if(count($controller) != 2) continue;

            $object = new \ReflectionClass($controller[0]);
            $method = $object->getMethod($controller[1]);

            foreach ($reader->getMethodAnnotations($method) as $object) {
                if($object instanceof MenuItem){
                    if(is_null($object->path)){
                        $object->path = $routeName;
                        $object->controller = $controller;
                    }
                    $this->data['menus'][$object->menu][] = $object->__toArray();
                } else
                if($object instanceof MenuParent){
                    $this->data['active'][$routeName] = $object->parent;
                }
            }
        }
        foreach ($this->data['menus'] as $menuName => &$menuItems){
            $this->sortMenuItems($menuItems);
            $menuItems = $this->buildTree($menuItems);
        }
    }

    /**
     * @param array $items
     */
    protected function sortMenuItems(array &$items){
        foreach($items as &$item)
            if(is_array($item['items']) && !empty($item['items']))
                $this->sortMenuItems($item['items']);

        uasort($items, function (array $first, array $second) {
            return (int) $first['order'] > (int) $second['order'] ? 1 : -1;
        });
    }

    /**
     * @return
     */
    protected function buildTree(array &$items, $parent = 'default') {
        $branch = array();

        $activeMenuName = $this->getActiveMenuName();

        foreach ($items as $key => $item) {
            $item['active'] =
                ($this->activeRouteName && $this->activeRouteName == $item['path']) ||
                ($activeMenuName && $activeMenuName == $item['name']);

            if ($item['parent'] == $parent) {
                $children = $this->buildTree($items, $item['name']);
                if ($children) {
                    $item['items'] = $children;
                }
                $branch[$key] = $item;
                unset($items[$key]);
            }
        }
        return $branch;
    }

    /**
     * @return mixed
     */
    public function getActiveMenuName(){
        if(is_array($this->data) && array_key_exists('active', $this->data))
            if(array_key_exists($this->activeRouteName, $this->data['active']))
                return $this->data['active'][$this->activeRouteName];
        return null;
    }
}