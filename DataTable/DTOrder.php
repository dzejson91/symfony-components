<?php
/**
 * Created by PhpStorm.
 * User: Krystian
 * Date: 21.06.2018
 * Time: 19:34
 */

namespace JasonMx\Components\DataTable;

class DTOrder
{
    public $type = 'number';
    public $id;
    public $value;
    public $name = 'order';

    /**
     * DTToggle constructor.
     * @param $id
     * @param $value
     */
    public function __construct($id, $value){
        $this->id = $id;
        $this->value = $value;
    }
}