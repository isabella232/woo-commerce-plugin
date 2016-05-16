<?php
namespace core;

abstract class Log
{

    private static $filename = '';

    public static function getFileName()
    {
        $filePath = CAMPAIGN_MONITOR_WOOCOMMERCE_DIR . '/var/log/System.log';
        try{
            if (file_exists(self::$filename)) {
                chmod(self::$filename, 0777);
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

    public static function write($message)
    {

        $file = self::getFileName();

        $date = self::getTimestamp();
        file_put_contents($file, '['.$date.']'. print_r($message, true) . PHP_EOL, FILE_APPEND);
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