<?php

/**
 * @author Krystian Jasnos <dzejson91@gmail.com>
 */

namespace JasonMx\Components\Form\Field;

use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Class KCFinderType
 * @package JasonMx\Components\Form\Field
 */
class KCFinderType extends TextType
{
    public function getBlockPrefix(){
        return 'kcfinder';
    }
}