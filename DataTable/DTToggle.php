<?php
/**
 * Created by PhpStorm.
 * User: Krystian
 * Date: 21.06.2018
 * Time: 19:34
 */

namespace JasonMx\Components\DataTable;

class DTToggle
{
    public $type = 'toggle';
    public $id;
    public $checked;
    public $name = 'active';

    /**
     * DTToggle constructor.
     * @param $id
     * @param $checked
     */
    public function __construct($id, $checked){
        $this->id = $id;
        $this->checked = $checked;
    }
}