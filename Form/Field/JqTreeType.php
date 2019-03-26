<?php

/**
 * @author Krystian Jasnos <dzejson91@gmail.com>
 */

namespace JasonMx\Components\Form\Field;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class JqTreeType extends AbstractType
{
    /** @var PropertyAccessor $accessor */
    protected $accessor;

    protected $viewVars = array(
        'data_url',
        'max_depth',
    );

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('json', HiddenType::class, array(
            'mapped' => false,
        ));

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function(FormEvent $event) use ($options)
            {
                $jsonData = $event->getForm()->get('json')->getData();
                $jsonData = isset($jsonData) ? json_decode($jsonData) : array();

                if(empty($jsonData)) return;

                $parents = $orders = $counter = array();
                foreach($jsonData as $order => $item){
                    if(isset($item->id)){
                        $parents[$itemId = intval($item->id)] = $parent = isset($item->parent) ? intval($item->parent) : null;
                        $counter[$parent] = isset($counter[$parent]) ? $counter[$parent]+1 : 0;
                        $orders[$itemId] = $counter[$parent];
                    }
                }
                unset($jsonData, $counter);

                /** @var Collection $items */
                $items = array();
                foreach($event->getForm()->getData() as $item){
                    $id = $this->accessor->getValue($item, $options['property_id']);
                    $items[$id] = $item;
                }

                foreach ($items as $itemId => $item){
                    if(array_key_exists($itemId, $parents)){
                        $parentId = $parents[$itemId];
                        $parent = array_key_exists($parentId, $items) ? $items[$parentId] : null;
                        $this->accessor->setValue($item, $options['property_parent'], $parent);
                        $this->accessor->setValue($item, $options['property_order'], $orders[$itemId]);
                    }
                }
            }
        );
    }

    protected function generateArrayData($data, array $options = array())
    {
        if(is_null($data)) return array();

        $results = array();
        foreach ($data as $item)
        {
            $name = $this->accessor->getValue($item, $options['property_title']);
            $name = htmlspecialchars(strip_tags($name));

            $results[] = array(
                'id' => $id = $this->accessor->getValue($item, $options['property_id']),
                'name' => sprintf('<i class="%s"></i> %s',
                        $options['item_icon_class'],
                        $name
                    ),
                'children' => $this->generateArrayData(
                        $this->accessor->getValue($item, $options['property_children']),
                        $options
                    ),
            );
        }
        return $results;
    }

    /**
     * @inheritDoc
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        foreach($this->viewVars as $viewVar)
            $view->vars[$viewVar] = $options[$viewVar];

        /** @var ArrayCollection $data */
        $data = $form->getData();
        if($data instanceof Collection)
        {
            $data = $data->filter(function ($item) {
                return $item->getParent() === null;
            });
        }

        $view->vars['jsonData'] = json_encode($this->generateArrayData($data, $options));

        parent::buildView($view, $form, $options);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'property_children' => 'children',
            'property_parent' => 'parent',
            'property_order' => 'order',
            'property_title' => 'title',
            'property_id' => 'id',
            'data_class' => Collection::class,
            'label' => false,
            'data_url' => null,
            'max_depth' => 1,
            'item_icon_class' => 'glyphicon glyphicon-link',
        ));
    }

    public function getBlockPrefix()
    {
        return 'jq_tree';
    }
}