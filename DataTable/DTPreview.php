<?php
/**
 * Created by PhpStorm.
 * User: Krystian
 * Date: 21.06.2018
 * Time: 19:21
 */

namespace JasonMx\Components\DataTable;

class DTPreview extends DTLink
{
    public function __construct($url){
        parent::__construct($url, null, 'preview', 'glyphicon glyphicon-eye-open');
    }
}