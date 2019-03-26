<?php
/**
 * @author Krystian Jasnos <dzejson91@gmail.com>
 */

namespace JasonMx\Components\Traits;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Class FormFieldOrderTrait
 * @package JasonMx\Components\Traits
 */
trait FormFieldOrderTrait
{
    /**
     * @return mixed
     */
    abstract function getFieldsOrder();

    /**
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        /** @var FormView[] $fields */
        $fields = [];
        foreach ($this->getFieldsOrder() as $field) {
            if ($view->offsetExists($field)) {
                $fields[$field] = $view->offsetGet($field);
                $view->offsetUnset($field);
            }
        }

        $view->children = $fields + $view->children;

        parent::finishView($view, $form, $options);
    }
}