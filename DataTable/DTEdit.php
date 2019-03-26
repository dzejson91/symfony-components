<?php
/**
 * Created by PhpStorm.
 * User: Krystian
 * Date: 21.06.2018
 * Time: 19:21
 */

namespace JasonMx\Components\DataTable;

class DTEdit extends DTLink
{
    public function __construct($url){
        parent::__construct($url, null, 'edit', 'glyphicon glyphicon-edit');
    }
}