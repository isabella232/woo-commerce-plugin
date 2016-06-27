<?php
namespace core;

abstract class Log
{

    private static $filename = '';

    public static function getFileName()
    {
        $filePath = CAMPAIGN_MONITOR_WOOCOMMERCE_DIR . 'var/log/system.log';

        try{
            if (file_exists($filePath)) {
                chmod($filePath, 0777);
                self::$filename = $filePath;
            } else {
                $handle = fopen($filePath, 'w');
                fclose($handle);
                self::$filename = $filePath;
                chmod(self::$filename, 0777);
            }
        } catch(Exception $e){
            echo $e->getMessage();
        }
        return self::$filename;
    }

    public static function warning($message)
    {

    }

    public static function getContent($options = array())
    {
        $content = file_get_contents(self::getFileName());
        $content = nl2br($content);
        return $content;
    }

    public static function clear($options = array())
    {
        self::write("",null);
    }

    public static function write($message, $option = FILE_APPEND)
    {
        $file = self::getFileName();
        $date = self::getTimestamp();
        $message = '['.$date.'] ' . print_r($message, true) . PHP_EOL;
        file_put_contents($file,  $message, $option);

    }

    private static function getTimestamp()
    {

        date_default_timezone_set('UTC');

        list($usec, $sec) = explode(' ', microtime());
        $usec = substr($usec, 2, 6);
        $datetime_now = date('Y-m-d H:i:s\.', $sec).$usec;
        $date = new \DateTime($datetime_now, new \DateTimeZone( 'UTC' ));
        return $date->format('Y-m-d G:i:s.u');
    }

}