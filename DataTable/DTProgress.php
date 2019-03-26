<?php
/**
 * Created by PhpStorm.
 * User: Krystian
 * Date: 21.06.2018
 * Time: 19:21
 */

namespace JasonMx\Components\DataTable;

class DTProgress
{
    public $type = 'progress';
    public $value;

    public function __construct($value){
        $this->value = intval($value);
    }
}