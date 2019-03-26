<?php
/**
 * Created by PhpStorm.
 * User: Krystian
 * Date: 21.06.2018
 * Time: 19:21
 */

namespace JasonMx\Components\DataTable;

class DTRemove extends DTLink
{
    public $confirm = true;
    public $class = 'text-danger';

    public function __construct($url){
        parent::__construct($url, null,'remove', 'glyphicon glyphicon-remove');
    }
}