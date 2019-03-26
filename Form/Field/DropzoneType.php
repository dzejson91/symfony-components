<?php

/**
 * @author Krystian Jasnos <dzejson91@gmail.com>
 */

namespace JasonMx\Components\Form\Field;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DropzoneType
 * @package JasonMx\Components\Form\Field
 */
class DropzoneType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        
        $resolver
            ->setDefaults(array(
                'label' => false,
                'multiple' => false,
                'required' => false,
                'mapped' => false,
                'list_url' => null,
            ))
            ->setRequired(array(
                'upload_url',
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['unique'] = substr(SHA1(microtime()), -10);
        $view->vars['upload_url'] = $options['upload_url'];
        $view->vars['list_url'] = isset($options['list_url']) ? $options['list_url'] : $options['upload_url'];
        parent::buildView($view, $form, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return FileType::class;
    }

}