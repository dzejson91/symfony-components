<?php
/**
 * Created by PhpStorm.
 * User: Krystian
 * Date: 18.10.2018
 * Time: 20:11
 */

namespace JasonMx\Components\Menu;


use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\KernelEvents;

class MenuExtension
{
    public static function load(ContainerBuilder $containerBuilder)
    {
        $containerBuilder
            ->setDefinition(MenuService::class, new Definition(
                MenuService::class,
                array(
                    new Reference('service_container')
                )
            ));

        $containerBuilder
            ->autowire(MenuListener::class)
            ->addTag('kernel.event_listener', array('event' => KernelEvents::CONTROLLER));

        $containerBuilder
            ->setDefinition('component.menu.twig', new Definition(
                MenuTwigExtension::class,
                array(
                    new Reference(MenuService::class),
                )
            ))
            ->addTag('twig.extension');
    }
}