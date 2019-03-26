<?php

/**
 * @author Krystian Jasnos <dzejson91@gmail.com>
 */
namespace JasonMx\Components\Traits;

use BaseBundle\Helpers\Helper;
use Symfony\Component\Intl\Exception\MethodNotImplementedException;

/**
 * Trait HelperTrait
 * @package JasonMx\Components\Traits
 *
 * @method isSuperUser()
 * @method isPanelRoute(string $path)
 */
trait HelperTrait
{
    public function __call($name, $arguments)
    {
        if(method_exists(Helper::class, $name)){
            return call_user_func_array(array(Helper::class, $name), $arguments);
        }

        throw new MethodNotImplementedException($name);
    }
}