<?php

/**
 * @author Krystian Jasnos <dzejson91@gmail.com>
 */

namespace JasonMx\Components\Extend;

use Assetic\Factory\LazyAssetManager;
use BaseBundle\Service\ConfigService;
use JasonMx\Components\Helper\ArrayHelper;
use JasonMx\Components\Implement\EntityHistoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use JasonMx\Components\Http\ApiResponse;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\UnitOfWork;
use MailBundle\Entity\Template;
use MailBundle\Repository\MailTemplateRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Intl\Exception\NotImplementedException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use UsersBundle\Entity\AbstractUser;

/**
 * Class AppController
 * @package JasonMx\Components\Extend
 */
abstract class AppController extends Controller
{
    // Typy komunikatów
    const MSG_TYPE_SUCCESS = 'success';
    const MSG_TYPE_ERROR = 'error';
    const MSG_TYPE_WARNING = 'warning';
    const MSG_TYPE_INFO = 'info';

    // Komunikaty do translacji
    const MSG_TEXT_SAVE_OK = 'Zapisano zmiany';
    const MSG_TEXT_SAVE_ERROR = 'Nie można zapisać zmian';
    const MSG_TEXT_UPDATE_COUNT = 'Ilość zaktualizowanych elementów: {count}';
    const MSG_TEXT_FORM_ERROR = 'Popraw błędy formularza';
    const MSG_TEXT_STATUS_CHANGE = 'Zmieniono status';
    const MSG_TEXT_ORDER_CHANGE = 'Zmieniono kolejność';
    const MSG_TEXT_NOT_FOUND = 'Nie znaleziono elementu';
    const MSG_TEXT_FILE_NOT_FOUND = 'Nie znaleziono pliku';
    const MSG_TEXT_NO_DATA = 'Nie przesłano danych';
    const MSG_TEXT_NO_CHANGES = 'Nie wykryto zmian';
    const MSG_TEXT_REMOVE_OK = 'Usunięto element';
    const MSG_TEXT_REMOVE_ERROR = 'Nie można usunąć';
    const MSG_TEXT_CHILD_EXISTS = 'Istnieją jeszcze podrzędne elementy';
    const MSG_TEXT_LOOP = 'Wystąpiło zapętlenie elementu';
    const MSG_TEXT_DISABLED = 'Element nieaktywny';
    const MSG_TEXT_BAD_CREDENTIALS = 'Brak dostępu';
    const MSG_TEXT_LOCKED = 'Element zablokowany';

    // Komunikaty błędów do translacji
    const MSG_EX_UNIDENTIFIED = 'Wystąpił nieznany błąd';
    const MSG_EX_DUPLICATE = 'Nie można zapisać, duplikacja danych';
    const MSG_EX_RESTRICT = 'Nie można usunąć, element jest używany';
    const MSG_EX_USER_DISABLED = 'Konto jest wyłączone / zablokowane';
    const MSG_EX_VALUE = 'Niepoprawna wartość';
    const MSG_EX_PARENT = 'Niepoprawny nadrzędny element, możliwe zapętlenie';
    const MSG_EX_FORBIDDEN = 'Brak odpowiednich uprawnień';
    const MSG_EX_NOT_FOUND = 'Nie znaleziono strony';
    const MSG_EX_DEMO = 'Nie można wykonać akcji, konto demonstracyjne';

    // Pola do aktualizacji z listy
    const FIELD_ACTIVE = 1;
    const FIELD_ORDER = 2;

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager(){
        return $this->getDoctrine()->getManager();
    }

    /**
     * @param $name
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository($name){
        return $this->getEntityManager()->getRepository($name);
    }

    /**
     * @return ValidatorInterface
     */
    public function getValidator(){
        return $this->get('validator');
    }

    /**
     * @return \AppKernel
     */
    public function getKernel()
    {
        return $this->get('kernel');
    }

    /**
     * @return boolean
     */
    public function isDebugMode(){
        return $this->getKernel()->isDebug();
    }

    /**
     * Get a user token
     * @return TokenInterface
     */
    protected function getUserToken()
    {
        if (!$this->container->has('security.token_storage')) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }

        if (null === $token = $this->container->get('security.token_storage')->getToken()) {
            return;
        }

        return $token;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getConfig($key, $default = null){
        return ConfigService::getInstance()->getValue($key, $default);
    }

    /**
     * @return boolean
     */
    public function validateValue($value){
        $constraints = func_get_args();
        array_shift($constraints);
        $validator = $this->getValidator();
        $errors = $validator->validate($value, $constraints);
        return !$errors->count();
    }

    /**
     * @ param mixed $entity
     */
    public function persistEntities(){
        if($entities = func_get_args()){
            $em = $this->getEntityManager();
            foreach($entities as $entity){
                $em->persist($entity);
            }
        }
    }

    /**
     * @throws
     */
    public function flushEntities(){
        $this->getEntityManager()->flush();
    }

    /**
     * @throws
     */
    public function persistAndFlush(){
        if($entities = func_get_args()){
            $em = $this->getEntityManager();
            foreach($entities as $entity){
                $em->persist($entity);
            }
            $em->flush();
        }
    }

    /**
     * @param array|ArrayCollection $arrayOfEntities
     * @throws
     */
    public function persistAndFlushArray($arrayOfEntities){
        if(empty($arrayOfEntities)) return;
        $em = $this->getEntityManager();
        foreach($arrayOfEntities as $entity){
            $em->persist($entity);
        }
        $em->flush();
    }

    /**
     * @throws
     */
    public function removeEntities(){
        if($entities = func_get_args()){
            $em = $this->getEntityManager();
            foreach($entities as $entity){
                $em->remove($entity);
            }
            $em->flush();
        }
    }

    /**
     * @param EntityManager $em
     * @param $initItems
     * @param $finalItems
     * @throws
     */
    public function removeCollection(EntityManager $em, $initItems, $finalItems){
        if(empty($initItems)) return;
        if(!$initItems instanceof ArrayCollection) {
            $initItems = new ArrayCollection($initItems);
        }
        if(!$finalItems instanceof ArrayCollection) {
            $finalItems = new ArrayCollection($finalItems);
        }
        foreach($initItems as $initItem){
            if(!$finalItems->contains($initItem)){
                $em->remove($initItem);
            }
        }
    }

    /**
     * @return TranslatorInterface
     */
    public function getTranslator(){
        return $this->get('translator');
    }

    /**
     * @return string
     */
    public function trans(){
        $translator = $this->getTranslator();
        return call_user_func_array(array($translator, 'trans'), func_get_args());
    }

    /**
     * @param $type
     * @param $message
     * @param array $parameters
     * @param null $domain
     * @param null $locale
     */
    public function addTransFlash($type, $message, array $parameters = array(), $domain = null, $locale = null)
    {
        parent::addFlash($type, $this->getTranslator()->trans($message, $parameters, $domain, $locale));
    }

    /**
     * @param $name
     * @param array $parameters
     * @param $referenceType
     * @return string
     */
    public function generateUrlProd($name, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        /** @var Router $router */
        $router = $this->container->get('router');

        /** @var Kernel $kernel */
        $kernel = $this->get('kernel');
        if($kernel->getEnvironment() != \AbstractKernel::ENV_PROD)
        {
            $baseUrl = $router->getContext()->getBaseUrl();
            $router->getContext()->setBaseUrl(dirname($baseUrl));
            $url = $router->generate($name, $parameters, $referenceType);
            $router->getContext()->setBaseUrl($baseUrl);
            return $url;
        } else {
            return $router->generate($name, $parameters, $referenceType);
        }
    }

    /**
     * @param Request $request
     * @param bool $different
     * @param bool $default
     * @return RedirectResponse
     */
    public function redirectToReferrer(Request $request, $different = true, $default = null)
    {
        if($referer = $request->headers->get('referer'))
        {
            if($different)
            {
                $lastPath = substr($referer, strlen($request->getSchemeAndHttpHost()));
                if($lastPath == $request->getRequestUri())
                    $referer = null;
            }
        }

        if(!$referer){
            $referer = isset($default) ? $default : $request->getBaseUrl();
        }

        return $this->redirect($referer);
    }

    /**
     * @inheritdoc
     */
    public function createFormAjax($type, $data = null, array $options = array())
    {
        $options = array_replace_recursive($options, array('attr' => array('data-ajax' => null)));
        if(!array_key_exists('action', $options) && array_key_exists('REQUEST_URI', $_SERVER)){
            $options['action'] = $_SERVER['REQUEST_URI'];
        }
        return parent::createForm($type, $data, $options);
    }

    /**
     * @inheritdoc
     */
    protected function createFormBuilderAjax($data = null, array $options = array())
    {
        $options = array_replace_recursive($options, array('attr' => array('data-ajax' => null)));
        if(!array_key_exists('action', $options) && array_key_exists('REQUEST_URI', $_SERVER)){
            $options['action'] = $_SERVER['REQUEST_URI'];
        }
        return parent::createFormBuilder($data, $options);
    }

    /**
     * Check if is JSON request
     *
     * @param Request $request
     * @return bool
     */
    protected function isJsonResponse(Request $request){
        $acceptHeader = strtolower($request->headers->get('Accept'));
        return (0 === strpos($acceptHeader, 'application/json'));
    }

    /**
     * @param \Exception $e
     * @param string $default
     * @return string|null
     */
    public function parseException(\Exception $e, $default = null){
        switch($class = get_class($e))
        {
            case 'Doctrine\DBAL\Exception\UniqueConstraintViolationException':
                return self::MSG_EX_DUPLICATE;

            case 'Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException':
                return self::MSG_EX_RESTRICT;

            case 'Symfony\Component\Security\Core\Exception\DisabledException':
                return self::MSG_EX_USER_DISABLED;

            case 'JasonMx\Components\Exception\LockException':
                return self::MSG_TEXT_LOCKED;

            case 'JasonMx\Components\Exception\ChildExistException':
                return self::MSG_TEXT_CHILD_EXISTS;

            case 'JasonMx\Components\Exception\LoopException':
                return self::MSG_TEXT_LOOP;

            case 'Symfony\Component\Filesystem\Exception\FileNotFoundException':
                return self::MSG_TEXT_FILE_NOT_FOUND;

            case 'Symfony\Component\Security\Core\Exception\AuthenticationServiceException':
            case 'Symfony\Component\Security\Core\Exception\BadCredentialsException':
                return self::MSG_TEXT_BAD_CREDENTIALS;
        }
        return $default;
    }

    /**
     * @param \Exception $e
     * @param string $default
     */
    public function addFlashException(\Exception $e, $default = self::MSG_EX_UNIDENTIFIED)
    {
        $parseMsg = $this->parseException($e);
        if(isset($parseMsg))
        {
            $this->addTransFlash(self::MSG_TYPE_ERROR, $parseMsg);
        } else {
            $this->addTransFlash(self::MSG_TYPE_ERROR, $default);
            $this->addFlash(self::MSG_TYPE_ERROR, get_class($e));
            $this->addFlash(self::MSG_TYPE_ERROR, $e->getMessage());
        }
    }

    /**
     * @param FormInterface $form
     * @return self
     */
    public function showFormErrors(FormInterface $form)
    {
        foreach($form->getErrors(true, true) as $formError){
            $propertyPath = $formError->getCause()->getPropertyPath();
            $this->addFlash('error', sprintf("%s - %s", $propertyPath, $formError->getMessage()));
        }
    }

    /**
     * @param Session|null $session
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function dataTableResponse(Session $session = null){
        if(is_null($session)){
            $session = $this->get('session');
        }
        $response = new ApiResponse();
        $response->refreshDataTable();
        $response->refreshJqTree();
        $response->addFlashes($session);
        return $response->get();
    }

    /**
     * @param array $variables
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderFrontendDemo(array $variables = array()){
        return $this->render('dashboard.twig', $variables);
    }

    /**
     * @param $templateName
     * @throws \Exception
     */
    public function sendEmail($templateName)
    {
        /** @var MailTemplateRepository $mailTplRepo */
        $mailTplRepo = $this->getRepository(Template::class);

        $mailTpl = $mailTplRepo->findOneBy(array(
            'name' => $templateName,
        ));

        if(!$mailTpl){
            throw new \Exception(sprintf('Mail template "%s" not found!', $templateName));
        }
    }

    /**
     * @param string $entityClass
     * @param int $entityId
     * @return
     */
    public function findEntity($entityClass, $entityId)
    {
        /** @var EntityRepository $entityRepo */
        $entityRepo = $this->getRepository($entityClass);
        return $entityRepo->find(intval($entityId));
    }

    /**
     * @param Request $request
     * @param string $entityName
     * @param int $updateFields
     * @return JsonResponse
     * @throws
     */
    public function _updateEntity(Request $request, $entityName, $updateFields)
    {
        try
        {
            $validator = $this->getValidator();

            /** @var EntityRepository $repository */
            $repository = $this->getRepository($entityName);

            $uow = $this->getEntityManager()->getUnitOfWork();

            $updateCounter = 0;

            if(is_array($data = $request->get('data', array())) && !empty($data))
            {
                foreach ($data as $id => $columns)
                {
                    $entity = $repository->find($id);

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
     * @param string $entityClassName
     * @param int $id
     * @return Response
     */
    public function _removeEntity(Request $request, $entityClassName, $id)
    {
        if($entity = $this->findEntity($entityClassName, $id))
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

    /**
     * @param ContainerAwareCommand $command
     * @param array $params
     * @return string
     * @throws
     */
    public function runCommand(ContainerAwareCommand $command, array $params = array())
    {
        array_unshift($params, '');
        $input = new ArgvInput($params);
        $output = new BufferedOutput();
        $command->setContainer($this->container);
        $command->run($input, $output);
        return $output->fetch();
    }

    /**
     * @param $entity
     * @param array &$changes
     * @return bool
     */
    public function isEntityChange($entity, &$changes = array())
    {
        $uow = $this->getEntityManager()->getUnitOfWork();
        if($uow->getEntityState($entity) === UnitOfWork::STATE_NEW){
            return true;
        }
        $uow->computeChangeSets();
        return $uow->isEntityScheduled($entity) || !empty($uow->getScheduledEntityUpdates());
    }

    /**
     * Save variable to session
     *
     * @param Request $request
     * @param $key
     * @param $value
     */
    public function setSessionValue(Request $request, $key, $value){
        $session = $request->getSession();
        $session->set($key, $value);
    }

    /**
     * Read variable from session
     *
     * @param Request $request
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function getSessionValue(Request $request, $key, $default = null){
        $session = $request->getSession();
        return $session->get($key, $default);
    }
}