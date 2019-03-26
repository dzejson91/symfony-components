<?php

/**
 * @author Krystian Jasnos <dzejson91@gmail.com>
 */

namespace JasonMx\Components\Form\Field;

use LangBundle\Translation\Translator;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CheckboxToggleType
 * @package JasonMx\Components\Form\Field
 */
class CheckboxToggleType extends CheckboxType
{
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        
        $resolver->setDefaults(array(
            'required' => false,
            'attr' => array(
                'class' => 'btn-sm btn-flat btn-success',
                'data-toggle' => 'toggle',
                'data-size' => 'small',
                'data-onstyle' => 'success',
                'data-offstyle' => 'danger',
                'data-style' => 'android',
                'data-on' => $this->translator->trans('Tak'),
                'data-off' => $this->translator->trans('Nie'),
            )
        ));
    }
}