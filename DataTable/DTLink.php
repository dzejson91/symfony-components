<?php
/**
 * Created by PhpStorm.
 * User: Krystian
 * Date: 21.06.2018
 * Time: 19:12
 */

namespace JasonMx\Components\DataTable;

class DTLink
{
    public $type = 'link';

    public $url = '#';

    public $title;

    public $text;

    public $icon;

    public $ajax = true;

    /**
     * DTLink constructor.
     * @param string $url
     * @param string $title
     * @param $icon
     */
    public function __construct($url, $text = null, $title = null, $icon = null){
        $this->url = $url;
        $this->text = $text;
        $this->title = $title;
        $this->icon = $icon;
    }
}