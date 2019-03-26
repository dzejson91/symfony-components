<?php

/**
 * @author Krystian Jasnos <dzejson91@gmail.com>
 */

namespace JasonMx\Components\Form\Field;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SelectTagType extends ChoiceType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'multiple' => true,
            //'translation_domain' => false,
            'choice_translation_domain' => false,
            'attr' => array(
                'class' => 'select2-tags',
                'multiple' => null,
            )
        ));
    }
}