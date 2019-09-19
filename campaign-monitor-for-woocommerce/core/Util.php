<?php

namespace core;

class Util {
    public static function htmlDecodeEncode($str)
    {
        $str=str_replace(array("&lsquo;","&rsquo;","&#8216;","&#8217;", "&sbquo;", "&#8218;"),"'",$str);
        $str=str_replace(array("&ldquo;","&rdquo;","&#8220;","&#8221;", "&bdquo;", "&#8222;"),'"',$str);

        $decoded=html_entity_decode($str, ENT_QUOTES);
        while ($decoded!=$str)
        {
            $str=$decoded;
            $decoded=html_entity_decode($str, ENT_QUOTES);
        }
        return htmlentities($decoded, ENT_QUOTES);
    }
}
