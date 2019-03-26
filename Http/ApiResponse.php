<?php

/**
 * @author Krystian Jasnos <dzejson91@gmail.com>
 */

namespace JasonMx\Components\Http;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class ApiResponse
 * @package JasonMx\Components\Http
 */
class ApiResponse
{
    /**
     * @var array
     */
    protected $data = array(
        'success' => true,
        'error' => null,
    );

    /**
     * ApiResponse constructor.
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        $this->data = $data;
    }

    /**
     * @param array|mixed $key
     * @param mixed $value
     */
    public function set($key, $value = null)
    {
        if(is_array($key)){
            foreach($key as $keyName => $value)
                $this->set($keyName, $value);
        } else {
            $this->data[$key] = $value;
        }
    }

    /**
     * @param boolean $success
     * @return $this
     */
    public function success($success){
        $this->set('success', boolval($success));
        return $this;
    }

    /**
     * @param \Exception|string $error
     */
    public function error($error){
        $this->success(false);
        $message = $error instanceof \Exception ? $error->getMessage() : $error;
        $this->set('error', (string)$message);
    }

    /**
     * @param Session $session
     * @return $this
     */
    public function addFlashes(Session $session){
        $this->data = array_merge($this->data, array(
            'flashes' => $session->getFlashBag()->all(),
        ));
        return $this;
    }

    /**
     * @param string|null $hash
     * @return $this
     */
    public function refreshDataTable($hash = null){
        $this->data = array_merge($this->data, array(
            'dataTable' => array($hash),
        ));
        return $this;
    }

    /**
     * @return $this
     */
    public function refreshJqTree(){
        $this->data = array_merge($this->data, array(
            'jqTree' => '',
        ));
        return $this;
    }

    /**
     * @param Form $form
     * @param string $formKey
     * @return $this
     */
    public function assignFormErrors(Form $form, $formKey = 'form_errors')
    {
        $errorsArray = array();

        /** @var FormError[] $errors */
        $errors = $form->getErrors();
        foreach($errors as $error){
            $errorsArray[$error->getOrigin()->getName()] = $error->getMessage();
        }

        $this->data[$formKey] = $errorsArray;
        return $this;
    }

    /**
     * @return JsonResponse
     */
    public function get(){
        return new JsonResponse($this->data);
    }
}