<?php

/**
 * @author Krystian Jasnos <dzejson91@gmail.com>
 */

namespace JasonMx\Components\Form\Field;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SubmitSaveType
 * @package JasonMx\Components\Form\Field
 */
class SubmitSaveType extends SubmitType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        
        $resolver->setDefaults(array(
            'data' => 1,
            'attr' => array(
                'class' => 'btn-sm btn-flat btn-success',
                'icon' => 'glyphicon glyphicon-floppy-disk',
                'data-ajax-form' => null,
            )
        ));
    }
}