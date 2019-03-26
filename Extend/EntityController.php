<?php

/**
 * @author Krystian Jasnos <dzejson91@gmail.com>
 */

namespace JasonMx\Components\Extend;

use JasonMx\Components\DataTable\DataTable;
use JasonMx\Components\Helper\ArrayHelper;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class EntityController
 * @package JasonMx\Components\Extend
 */
abstract class EntityController extends AppController
{
    const ENTITY_CLASS_NAME = null;
    const ENTITY_CLASS_KEY = null;
    const ENTITY_FORM_CLASS_NAME = null;

    const ENTITY_DATATABLE_STRUCTURE_FUNCTION = 'getDataTableObject';
    const ENTITY_DATATABLE_DATA_FUNCTION = 'getDataForDataTable';

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    public function entityRepository(){
        return $this->getRepository($this::ENTITY_CLASS_NAME);
    }

    /**
     * @param EntityRepository $repository
     * @return DataTable
     */
    public function entityDataTable(EntityRepository $repository){
        $func = $this::ENTITY_DATATABLE_STRUCTURE_FUNCTION;

        /** @var DataTable $dataTable */
        $dataTable = $repository->$func();
        return $dataTable;
    }

    public function entityDataTableView(ArrayHelper $options){
        $repo = $this->entityRepository();

        $dataTable = $this->entityDataTable($repo);
        $dataTable->loadTranslations($this->getTranslator());
        $dataTable->routeAjax = $options->getString('route_ajax', sprintf('panel_%s_list_data', $this::ENTITY_CLASS_KEY));

        return $this->render($options->getString('view', 'entity/list.twig'), array_merge(array(
            'dataTable' => $dataTable,
            'classKey' => $this::ENTITY_CLASS_KEY,
            'boxTitle' => $options->getString('title', ucfirst($this::ENTITY_CLASS_KEY)),
            'include_before' => null,
            'include_after' => null,
            'btn_create' => true,
            'btn_save_changes' => true,
        ), $options->getArray('view_data', array())));
    }

    /**
     * @param Request $request
     * @param callable $callback
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Exception
     */
    public function entityDataTableData(Request $request, callable $callback){
        $func = $this::ENTITY_DATATABLE_DATA_FUNCTION;

        $repo = $this->entityRepository();
        $dataTable = $this->entityDataTable($repo);
        $dataTable->setRequest($request);
        $results = $repo->$func($dataTable);

        foreach($results as $result){
            $row = $callback($result);
            $dataTable->addRow($row);
        }

        return $dataTable->render();
    }

    public function entityEditView(Request $request, $id = null, ArrayHelper $options){
        $repo = $this->entityRepository();
        $entityName = $this::ENTITY_CLASS_NAME;

        $entity = isset($id) ? $repo->find(intval($id)) : new $entityName();
        if(!$entity instanceof $entityName){
            $this->addTransFlash(self::MSG_TYPE_ERROR, self::MSG_TEXT_NOT_FOUND);
            return new RedirectResponse($this->generateUrl($options->getString('route_list', sprintf('panel_%s_list', $this::ENTITY_CLASS_KEY))));
        }

        $formOptions = $options->get('formOptions', array());
        $form = $this->createFormAjax($this::ENTITY_FORM_CLASS_NAME, $entity, $formOptions);
        $form->handleRequest($request);

        if ($form->isSubmitted())
        {
            if ($form->isValid())
            {
                try {
                    if($isChanged = $this->isEntityChange($entity)){
                        $this->addTransFlash(self::MSG_TYPE_SUCCESS, self::MSG_TEXT_SAVE_OK);
                    } else {
                        $this->addTransFlash(self::MSG_TYPE_INFO, self::MSG_TEXT_NO_CHANGES);
                    }

                    $this->persistAndFlush($entity);

                    $submitBtnType = $form->has('submitButtonType') ? intval($form->get('submitButtonType')->getData()) : 0;
                    switch ($submitBtnType)
                    {
                        case AppFormType::BTN_SAVE_AND_BACK: {
                            return $this->redirectToRoute($options->getString('route_list', sprintf('panel_%s_list', $this::ENTITY_CLASS_KEY)));
                        } break;

                        default: {
                            return $this->redirectToRoute($options->getString('route_edit', sprintf('panel_%s_edit', $this::ENTITY_CLASS_KEY)), ['id' => $entity->getId()]);
                        } break;
                    }

                } catch (\Exception $e) {
                    $this->addFlashException($e, self::MSG_TEXT_SAVE_ERROR);
                }

            } else {
                $this->addTransFlash(self::MSG_TYPE_ERROR, self::MSG_TEXT_FORM_ERROR);
                foreach($form->getErrors() as $formError){
                    dump($formError);
                    $this->addTransFlash(self::MSG_TYPE_ERROR, $formError->getMessage());
                }
            }
        }

        return $this->render($options->getString('view', 'entity/edit.twig'), array_merge(array(
            'entity' => $entity,
            'form' => $form->createView(),
            'classKey' => $this::ENTITY_CLASS_KEY,
            'boxTitle' => $options->getString('title', ucfirst($this::ENTITY_CLASS_KEY)),
        ), $options->getArray('view_data', array())));
    }

    /**
     * @param Request $request
     * @param int $updateFields
     * @return JsonResponse
     * @throws
     */
    public function entityUpdate(Request $request, $updateFields)
    {
        try
        {
            $validator = $this->getValidator();

            /** @var EntityRepository $repository */
            $repository = $this->getRepository($this::ENTITY_CLASS_NAME);

            $uow = $this->getEntityManager()->getUnitOfWork();

            $updateCounter = 0;

            if(is_array($data = $request->get('data', array())) && !empty($data))
            {
                foreach ($data as $id => $columns)
                {
                    $entity = $repository->find(intval($id));

                    $item = new ArrayHelper($columns);

                    if(is_array($columns) && $entity)
                    {
                        $update = false;

                        if(self::FIELD_ORDER & $updateFields) {
                            $errors = $validator->validatePropertyValue($entity, 'order', $item->getInt('order'));
                            if(!$errors->count()){
                                $entity->setOrder($item->getInt('order'));
                                $update = true;
                            }
                        }

                        if(self::FIELD_ACTIVE & $updateFields) {
                            $errors = $validator->validatePropertyValue($entity, 'active', $item->getInt('active'));
                            if(!$errors->count()){
                                $entity->setActive($item->getBool('active'));
                                $update = true;
                            }
                        }

                        if($update)
                        {
                            $uow->computeChangeSets();
                            if($changes = $uow->getEntityChangeSet($entity)){
                                $updateCounter++;
                                $this->persistEntities($entity);
                            }
                        }
                    }
                }

                if($updateCounter > 0)
                {
                    $this->flushEntities();
                    $this->addTransFlash(self::MSG_TYPE_SUCCESS, self::MSG_TEXT_UPDATE_COUNT, array(
                        '{count}' => $updateCounter,
                    ));

                } else {
                    $this->addTransFlash(self::MSG_TYPE_INFO, self::MSG_TEXT_NO_CHANGES);
                }

            } else {
                $this->addTransFlash(self::MSG_TYPE_ERROR, self::MSG_TEXT_NO_DATA);
            }

        } catch (\Exception $e){
            $this->addFlashException($e, self::MSG_TEXT_SAVE_ERROR);
        }

        return $this->dataTableResponse();
    }

    /**
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function entityRemove(Request $request, $id)
    {
        if($entity = $this->findEntity($this::ENTITY_CLASS_NAME, intval($id)))
        {
            try
            {
                $this->removeEntities($entity);
                $this->addTransFlash(self::MSG_TYPE_SUCCESS, self::MSG_TEXT_REMOVE_OK);

            } catch (\Exception $e){
                $this->addFlashException($e, self::MSG_TEXT_REMOVE_ERROR);
            }

        } else {
            $this->addTransFlash(self::MSG_TYPE_ERROR, self::MSG_TEXT_NOT_FOUND);
        }

        return $request->isXmlHttpRequest() ? $this->dataTableResponse() : $this->redirectToReferrer($request);
    }
}