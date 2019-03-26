<?php

/**
 * @author Krystian Jasnos <dzejson91@gmail.com>
 */

namespace JasonMx\Components\DataTable;

/**
 * Class DataTableColumn
 * @package JasonMx\Components\Addon
 */
class DataTableColumn
{
    /**
     * @var string|null
     */
    protected $title = null;

    /**
     * @var string|null
     */
    protected $name = null;

    /**
     * @var string|null
     */
    protected $dataName = null;

    /**
     * @var string|null
     */
    protected $alias = null;

    /**
     * @var bool
     */
    protected $visible = true;

    /**
     * @var bool
     */
    protected $searchable = false;

    /**
     * false - brak sortowania
     * true - włączone sortowanie
     * -1 - domyślnie - desc
     * 1 - domyślnie - asc
     *
     * @var bool|integer
     */
    protected $sortable = false;

    /**
     * @var string
     */
    protected $colAlign = 'left';

    /**
     * @var boolean
     */
    protected $render = false;

    /**
     * @var null|string
     */
    protected $type = null;

    /**
     * @var bool|null
     */
    protected $escape = true;

    /**
     * DataTableColumn constructor.
     * @param array $properties
     */
    public function __construct(array $properties = array()){
        foreach($properties as $key => $value)
            if(property_exists($this, $key))
                $this->$key = $value;
    }

    /**
     * @return null|string
     */
    public function getAlias()
    {
        if(is_null($this->alias))
            return $this->name;
        return $this->alias;
    }

    /**
     * @return null|string
     */
    public function getTitle()
    {
        if(is_null($this->title))
            return $this->name;
        return $this->title;
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param null|string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return null|string
     */
    public function getDataName()
    {
        if(is_null($this->dataName))
            return $this->name;
        return $this->dataName;
    }

    /**
     * @return boolean
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * @return boolean
     */
    public function isSearchable()
    {
        return $this->searchable;
    }

    /**
     * @return boolean|integer
     */
    public function getSortable()
    {
        return $this->sortable;
    }

    /**
     * @return string
     */
    public function getColAlign()
    {
        return $this->colAlign;
    }

    /**
     * @return boolean
     */
    public function isRender()
    {
        return $this->render;
    }

    /**
     * @return null|string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param null|string $type
     * @return DataTableColumn
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isEscape()
    {
        return $this->escape;
    }

    /**
     * @param bool|null $escape
     * @return DataTableColumn
     */
    public function setEscape($escape)
    {
        $this->escape = $escape;
        return $this;
    }

}