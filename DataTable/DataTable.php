<?php

/**
 * @author Krystian Jasnos <dzejson91@gmail.com>
 */

namespace JasonMx\Components\DataTable;

use JasonMx\Components\Helper\StringHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class DataTable
 * @package JasonMx\Components\Addon
 */
class DataTable
{
    /**
     * Hash instancji
     *
     * @var null
     */
    protected $hash = null;

    /**
     * Url dla danych
     *
     * @var null
     */
    public $routeAjax = null;

    /**
     * Url dla danych
     *
     * @var null
     */
    public $routeParams = array();

    /**
     * Tablica translacji
     *
     * @var array
     */
    public $translations = array();

    /**
     * Kolumny tabeli
     *
     * @var DataTableColumn[]|ArrayCollection
     */
    protected $columns;

    /**
     * Dane tabeli - wiersze
     *
     * @var array
     */
    protected $rows = array();

    /**
     * Numer renderowania (zazwyczaj automatycznie powiększany)
     *
     * @var int|null
     */
    protected $numberDraw = null;

    /**
     * Ilość wszystkich rekordów
     *
     * @var int|null
     */
    protected $countAll = null;

    /**
     * Ilość wszystkich rekordów (przefiltrowanych)
     *
     * @var int|null
     */
    protected $countFiltered = null;

    /**
     * Domyślna ilość wyświetlanych danych
     *
     * @var int
     */
    protected $limit = 10;

    /**
     * Domyślna ilość początku danych
     * @var int
     */
    protected $offset = 0;

    /**
     * @var Request $request
     */
    protected $request;

    /**
     * DataTable constructor.
     * @param string $name
     */
    public function __construct($name){
        $this->hash = substr(SHA1(__CLASS__.$name), -10);
        $this->columns = new ArrayCollection();
    }

    /**
     * @param DataTableColumn $column
     * @return $this
     * @throws \Exception
     */
    public function addColumn(DataTableColumn $column){

        // jeśli nieustawiona nazwa to ustaw losową
        if(!$column->getName()){
            $column->setName(substr(SHA1(__CLASS__.$this->_hash . count($this->_columns)), -10));
        }

        // sprawdź unikalność nazw
        if($this->existColumn($column->getName()))
            throw new \Exception(sprintf('%s: Column "%s" already exist!', __CLASS__, $column->getName()));

        // dodaj kolumnę
        $this->columns->set($column->getName(), $column);
        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function existColumn($name){
        return $this->columns->containsKey($name);
    }

    /**
     * @param string $name
     * @return null
     */
    public function getColumn($name){
        if($this->existColumn($name))
            return $this->columns->get($name);
        return false;
    }

    /**
     * @param array $rowData
     * @throws \Exception
     */
    public function addRow(array $rowData)
    {
        // odczyt nazw kolumn
        static $columnNames = null;
        if(is_null($columnNames)){
            $columnNames = $this->columns->getKeys();
        }

        if(count($rowData) < $this->columns->count())
            throw new \Exception(sprintf('%s: Table row must have contains all keys: %s!', __CLASS__, implode(', ', $columnNames)));

        // sprawdzanie i przypisywanie do column
        $columnIterator = 0;
        $row = array();
        foreach($rowData as $column => $value)
        {
            if(is_numeric($column) && array_key_exists($columnIterator, $columnNames))
                $columnName = $columnNames[$columnIterator++]; else
                $columnName = $column;

            if(!$this->columns->containsKey($columnName))
                throw new \Exception(sprintf('Column "%s" not found!', $columnName));

            /** @var DataTableColumn $column */
            $column = $this->columns->get($columnName);

            if(is_string($value) && $column->isEscape()){
                $value = self::escape($value);
            }

            $row[$columnName] = $value;
        }

        $this->rows[] = $row;
    }

    /**
     * @param array $additionalData
     * @param bool|false $resetColumnNames
     * @return JsonResponse
     * @throws
     */
    public function render(array $additionalData = array(), $resetColumnNames = false){

        $resultData = array(
                'draw' => isset($this->numberDraw) ? $this->numberDraw : $this->getRequest()->get('draw', 1),
                'recordsTotal' => isset($this->countAll) ? $this->countAll : count($this->rows),
                'recordsFiltered' => isset($this->countFiltered) ? $this->countFiltered : count($this->rows),
                'data' => !$resetColumnNames ? $this->rows : array_map(function($row){
                    return array_values($row);
                }, $this->rows),
            );

        return new JsonResponse(array_merge($additionalData, $resultData));
    }

    /**
     * @param Query $query
     * @return Query
     * @throws
     */
    public function prepareLimits(Query $query){

        // limit & offset
        if(($this->limit = intval($this->getRequest()->get('length', $this->limit))) > 0){
            $query->setMaxResults($this->limit);
            $query->setFirstResult($this->offset = $this->request->get('start', $this->offset));
        } else {
            $query->setMaxResults(null);
            $query->setFirstResult(null);
        }

        return $query;
    }

    /**
     * Przetwarzenie ORM-a do wyszukiwania danych
     *
     * @param QueryBuilder $queryBuilder
     * @return QueryBuilder
     */
    public function prepareSearch(QueryBuilder $queryBuilder){

        if(!$search = $this->getRequest()->get('search')) return $queryBuilder;
        if(!strlen($search = $search['value'])) return $queryBuilder;

        $exprArr = array();

        foreach($this->columns as $column)
        {
            if($column->isSearchable() && $column->getAlias())
            {
                switch ($column->getType())
                {
                    case 'date':
                    case 'datetime': {
                        $exprArr[] = "COLLATE({$column->getAlias()}, utf8_general_ci) like :search";
                    } break;

                    default: {
                        $exprArr[] = "{$column->getAlias()} like :search";
                    }
                }
            }
        }

        if(!empty($exprArr)){
            $queryBuilder->andWhere(new Query\Expr\Orx($exprArr));
            $queryBuilder->setParameter('search', '%'.$search.'%');
        }

        return $queryBuilder;
    }

    /**
     * Domyślne sortowanie
     *
     * @return string
     */
    public function getJsonDefaultOrder()
    {
        $order = array();
        $columnIndex = 0;
        foreach($this->columns as $column){
            if(is_int($ord = $column->getSortable()))
                $order[] = array($columnIndex, $ord >= 0 ? 'asc' : 'desc');
            $columnIndex++;
        }
        return json_encode($order);
    }

    /**
     * Pobieranie sortowania (RAW QUERY)
     *
     * @param QueryBuilder $queryBuilder
     * @return QueryBuilder
     * @throws
     */
    public function prepareOrder(QueryBuilder $queryBuilder){
        $columnNames = $this->columns->getKeys();
        $ordering = $this->getRequest()->get('order', array());
        foreach($ordering as $index => $order)
            if($orderColumn = $this->columns->get($columnNames[$order['column']])->getAlias())
                $queryBuilder->addOrderBy($orderColumn, $order['dir'] == 'asc' ? ' ASC' : ' DESC');
        return $queryBuilder;
    }

    /**
     * @param TranslatorInterface $translator
     * @return array
     */
    public function loadTranslations(TranslatorInterface $translator){

        $this->translations = array(
            'lengthMenu' => $translator->trans('Wyświetl') . ': _MENU_',
            'search' => $translator->trans('Szukaj'),
            'zeroRecords' => $translator->trans('Brak danych'),
            'emptyTable' =>  $translator->trans('Brak danych'),
            'info' => $translator->trans('Wyświetlono: {start}-{end} z {total}', array(
                    '{start}' => '_START_',
                    '{end}' => '_END_',
                    '{total}' => '_TOTAL_',
                    '{page}' => '_PAGE_',
                    '{pages}' => '_PAGES_',
                )),
            'infoEmpty' => $translator->trans('Nic nie znaleziono'),
            'infoFiltered' => '(' . $translator->trans('szukano spośród') . ': _MAX_)',
            'searchPlaceholder' => $translator->trans('Słowo kluczowe'),
            'processing' => $translator->trans('Trwa odczyt danych...'),
            'loadingRecords' =>  $translator->trans('Trwa wczytywanie...'),
            'paginate' => array(
                'first' => '<span class="glyphicon glyphicon-backward"></span>',
                'last' => '<span class="glyphicon glyphicon-forward"></span>',
                'next' => '<span class="glyphicon glyphicon-chevron-right"></span>',
                'previous' => '<span class="glyphicon glyphicon-chevron-left"></span>',
            ),
            'aria' => array(
                'sortAscending' => $translator->trans('sortuj rosnąco'),
                'sortDescending' => $translator->trans('sortuj malejąco'),
            ),
            'words' => array(
                'yes' => $translator->trans('Tak'),
                'no' => $translator->trans('Nie'),
                'edit' => $translator->trans('Edytuj'),
                'remove' => $translator->trans('Usuń'),
                'preview' => $translator->trans('Podgląd'),
                'send' => $translator->trans('Wyślij'),
                'play' => $translator->trans('Uruchom'),
                'pause' => $translator->trans('Wstrzymaj'),
            ),
        );
        return $this->translations;
    }

    /**
     * @return null
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @return DataTableColumn[]|ArrayCollection
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param int $countAll
     */
    public function setCountAll($countAll)
    {
        $this->countAll = $countAll;
    }

    /**
     * @param int $countFiltered
     */
    public function setCountFiltered($countFiltered)
    {
        $this->countFiltered = $countFiltered;
    }

    /**
     * @return Request
     * @throws
     */
    public function getRequest()
    {
        if (!$this->request instanceof Request) {
            throw new \Exception('First set Request for DataTable!');
        }
        return $this->request;
    }

    /**
     * @param Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @return int
     * @throws
     */
    public function getJsonTranslations()
    {
        if(!$this->translations){
            throw new \Exception('First load translations!');
        }
        return json_encode($this->translations);
    }

    public static function escape($value){
        return StringHelper::simpleText($value);
    }
}