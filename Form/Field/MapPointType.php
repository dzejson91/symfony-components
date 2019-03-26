<?php

/**
 * @author Krystian Jasnos <dzejson91@gmail.com>
 */

namespace JasonMx\Components\Form\Field;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MapPointType
 * @package JasonMx\Components\Form\Field
 */
class MapPointType extends AbstractType
{
    protected $defaultPoint = array(
        'lat' => 51.76,
        'lng' => 21.25,
        'zoom' => 12,
        'centerLat' => 51.76,
        'centerLng' => 21.25,
    );

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('search', TextType::class, array(
                'mapped' => false,
                'label' => 'Wyszukaj',
                'attr' => array(
                    'placeholder' => 'Wyszukaj na mapie ...',
                ),
            ))
            ->add('lat', HiddenType::class, array(
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            ->add('lng', HiddenType::class, array(
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            ->add('zoom', HiddenType::class, array(
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            ->addModelTransformer(new CallbackTransformer(
                array($this, 'modelTransform'), array($this, 'modelReverseTransform')
            ));
    }

    public function modelTransform($value){
        return !empty($value) ? json_decode($value, true) : $this->defaultPoint;
    }

    public function modelReverseTransform($value){
        return json_encode($value);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
    }
}