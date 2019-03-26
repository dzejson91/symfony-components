<?php

/**
 * @author Krystian Jasnos <dzejson91@gmail.com>
 */

namespace JasonMx\Components\Extend;

use JasonMx\Components\Form\Field\CheckboxToggleType;
use JasonMx\Components\Traits\HelperTrait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class AppFormType
 * @package JasonMx\Components\Extend
 */
class AppFormType extends AbstractType
{
    use HelperTrait;

    const BTN_SAVE = 1;
    const BTN_SAVE_AND_BACK = 2;
    const BTN_SAVE_AND_NEW = 4;
    const BTN_SEND = 8;

    protected function createGroup(FormBuilderInterface $builder, $name, array $options = array())
    {
        $options = array_merge(array(
            'inherit_data' => true,
            'label' => false,
        ), $options);

        return $builder->create($name, FormType::class, $options);
    }

    protected function changeOptions(Form $field, array $options = array()){
        $parent  = $field->getParent();
        $opts    = $field->getConfig()->getOptions();
        $type    = get_class($field->getConfig()->getType()->getInnerType());
        $name    = $field->getName();
        return $parent->add($name, $type, array_merge($opts, $options));
    }

    protected function disableField(Form $field)
    {
        $this->changeOptions($field, array('disabled' => true));
    }

    protected function addLockChoice(FormBuilderInterface $form){
        if($this->isSuperUser()){
            $form->add('locked', CheckboxToggleType::class, array(
                'label' => 'Blokada usunięcia ~',
                'required' => false,
                'help_block' => '~ Opcja widoczna / dostępna tylko dla super admina',
                'help_icon' => null,
            ));
        }
        return $form;
    }

    protected function addSubmitButtons(FormBuilderInterface $builder, $types, array $options = array())
    {
        $submitButtons = $this->createGroup($builder, 'submitButtons', $options);

        $builder->add('submitButtonType', HiddenType::class, array(
            'data' => self::BTN_SAVE,
            'mapped' => false,
            'attr' => array(
                'class' => 'btnSubmitSaveValue',
            ),
        ));

        if(self::BTN_SAVE & $types)
        {
            $submitButtons->add('submitButtonSave', ButtonType::class, array(
                'label' => 'Zapisz',
                'attr' => array(
                    'value' => self::BTN_SAVE,
                    'class' => 'btn-sm btn-flat btn-success btnSubmitSave',
                    'icon' => 'glyphicon glyphicon-floppy-disk',
                ),
            ));
        }

        if(self::BTN_SAVE_AND_NEW & $types)
        {
            $submitButtons->add('submitButtonSaveAndAdd', ButtonType::class, array(
                'label' => 'Zapisz i dodaj nowy',
                'attr' => array(
                    'value' => self::BTN_SAVE_AND_NEW,
                    'class' => 'btn-sm btn-flat btn-success btnSubmitSave',
                    'icon' => 'glyphicon glyphicon-floppy-disk',
                ),
            ));
        }

        if(self::BTN_SAVE_AND_BACK & $types)
        {
            $submitButtons->add('submitButtonSaveAndBack', ButtonType::class, array(
                'label' => 'Zapisz i wróć',
                'attr' => array(
                    'value' => self::BTN_SAVE_AND_BACK,
                    'class' => 'btn-sm btn-flat btn-success btnSubmitSave',
                    'icon' => 'glyphicon glyphicon-floppy-disk',
                ),
            ));
        }

        if(self::BTN_SEND & $types)
        {
            $submitButtons->add('submitButtonSend', ButtonType::class, array(
                'label' => 'Wyślij',
                'attr' => array(
                    'value' => self::BTN_SEND,
                    'class' => 'btn-sm btn-flat btn-primary btnSubmitSave',
                    'icon' => 'glyphicon glyphicon-send',
                ),
            ));
        }

        $builder->add($submitButtons);
    }
}