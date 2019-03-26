<?php
/**
 * @author Krystian Jasnos <dzejson91@gmail.com>
 */

namespace JasonMx\Components\Helper;


class StringHelper
{
    public static function slug($string, $allowCSlash = true){

        $replace = self::slugReplaceArray();
        $string = str_replace(array_keys($replace), array_values($replace) , $string);
        $replace = self::slugCyrylicReplaceArray();
        $string = str_replace(array_keys($replace), array_values($replace) , $string);
        $string = strtolower($string);
        if($allowCSlash)
            $string = preg_replace('/[^0-9a-z\-\/]+/', '', $string); else
            $string = preg_replace('/[^0-9a-z\-]+/', '', $string);
        $string = preg_replace('/[\-]+/', '-', $string);
        $string = preg_replace('/[\/]+/', '/', $string);
        $string = trim($string, '-');
        $string = trim($string, '/');
        $string = stripslashes($string);
        return $string;
    }

    public static function simpleFileName($string)
    {
        $replace = self::slugReplaceArray();
        unset($replace['.']);
        $string = str_replace(array_keys($replace), array_values($replace) , $string);
        $string = str_replace(array_keys($replace), array_values($replace) , $string);
        $string = strtolower($string);
        $string = preg_replace('/[^0-9a-z\-\.]+/', '-', $string);
        $string = preg_replace('/[\-]+/', '-', $string);
        $string = preg_replace('/[\/]+/', '/', $string);
        $string = trim($string, '-');
        $string = trim($string, '/');
        $string = stripslashes($string);
        return $string;
    }

    /**
     * @param \DateTime $dateTime
     * @param string $format
     * @return string|null
     */
    public static function dateTimeFormat($dateTime, $format = 'Y-m-d H:i'){
        if($dateTime instanceof \DateTime){
            return $dateTime->format($format);
        }
        return null;
    }

    public static function slugReplaceArray(){
        return array(
            ' ' => '-',
            'Ą' => 'a', 'Ć' => 'c', 'Ę' => 'e', 'Ł' => 'l',
            'Ń' => 'n', 'Ó' => 'o', 'Ś' => 's', 'Ż' => 'z',
            'Ź' => 'z', 'Ž' => 'z', 'Ě' => 'e', 'Ř' => 'r',
            'Ů' => 'u', 'ů' => 'u', 'š' => 's', 'Š' => 's',
            'ť' =>'t', 'č' => 'c',
            'á' => 'a', 'â' => 'a', 'ä' => 'a', 'é' => 'e',
            'ë' => 'e', 'í' => 'i', 'î' => 'i', 'ó' => 'o',
            'ô' => 'o', 'ö' => 'o', 'ú' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ą' => 'a', 'ę' => 'e', 'ś' => 's',
            'ź' => 'z', 'ż' => 'z', 'ń' => 'n', 'ł' => 'l',
            'ć' => 'c', 'ž' => 'z', 'ě' => 'e', 'ř' => 'r',
            '.' => '-', ',' => '-', ';' => '-', '"' => '',
            "'" => '', '(' => '-', ')' => '-', '#' => '',
            '?' => '', '<' => '', '>' => '', '!' => '',
            '@' => 'a', '\$' => '', '^' => '', '*' => '',
        );
    }

    public static function slugCyrylicReplaceArray(){
        return array(
            'ы' => 'y', 'ж' => 'zh', 'ч' => 'ch', 'щ' => 'sht',
            'ш' => 'sh', 'ю' => 'yu', 'а' => 'a', 'б' => 'b',
            'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e',
            'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k',
            'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
            'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
            'ъ' => 'y', 'ь' => 'x', 'я' => 'q', 'Ж' => 'Zh',
            'Ч' => 'Ch', 'Щ' => 'Sht', 'Ш' => 'Sh', 'Ю' => 'Yu',
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G',
            'Д' => 'D', 'Е' => 'E', 'З' => 'Z', 'И' => 'I',
            'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M',
            'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R',
            'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F',
            'Х' => 'H', 'Ц' => 'c', 'Ъ' => 'Y', 'Ь' => 'X',
            'Я' => 'Q',
        );
    }

    /**
     * @param $content
     * @param array $fields
     * @return null|string|string[]
     */
    public static function removeBastards($content, $fields = array())
    {
        if(is_array($content)){
            foreach ($content as $key => &$value)
                if(empty($fields) || in_array($key, $fields))
                    $value = self::removeBastards($value, $fields);
            return $content;
        } else {
            return preg_replace("/(\S\&nbsp\;\S)+\s+/is","\\1&nbsp;", preg_replace("/\s(\S)\s+/is"," \\1&nbsp;", (string)$content));
        }
    }

    /**
     * @param $text
     * @param $minLength
     * @param string $delimiter
     * @param string $addon
     * @return string
     */
    public static function cutWords($text, $minLength, $delimiter = ' ', $addon = '...'){
        $words = explode($delimiter, $text);
        $output = current($words);
        while(strlen($output) < $minLength && $word = next($words))
            $output .= $delimiter.$word;
        return $output.(strlen($text) > strlen($output) ? $addon : '');
    }

    /**
     * @param $bytes
     * @param string $format
     * @param int $base
     * @return string
     */
    public static function formatBytes($bytes, $format = '%1.2f %s', $base = 1024){
        $prefix = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );
        $class = min((int)log($bytes , $base) , count($prefix) - 1);
        return sprintf($format , $bytes / pow($base, $class), $prefix[$class]);
    }

    /**
     * @param mixed $data
     * @return string
     */
    public static function simpleText($data)
    {
        if(is_array($data)){
            foreach ($data as &$value)
                $value = self::simpleText($value);
        }

        if(is_string($data)){
            $data = preg_replace('/&(#[0-9]+|[a-z]+);/i', '', $data);
            $data = strip_tags($data);
            $data = htmlspecialchars($data);
            return trim($data);
        }
    }

}