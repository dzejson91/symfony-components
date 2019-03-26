<?php

/**
 * @author Krystian Jasnos <dzejson91@gmail.com>
 */

namespace JasonMx\Components\Menu\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Class MenuParent
 * @package JasonMx\Components\Menu\Annotation
 *
 * @Annotation
 */
class MenuParent
{
    /**
     * @var string $parent
     */
    public $parent;
}