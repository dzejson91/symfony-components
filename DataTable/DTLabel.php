<?php
/**
 * Created by PhpStorm.
 * User: Krystian
 * Date: 21.06.2018
 * Time: 19:21
 */

namespace JasonMx\Components\DataTable;

class DTLabel
{
    public $type = 'label';
    public $text;
    public $class;

    public function __construct($text, $class = 'default'){
        $this->text = $text;
        $this->class = $class;
    }
}