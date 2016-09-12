<?php

namespace core;

define('LOCK_DIR', dirname(__FILE__));
define('LOCK_SUFFIX', '.lock');

class CronHelper
{
    private static $pid;

    private function __construct()
    {
    }

    function __clone()
    {
    }

    private static function isRunning()
    {
        $pids = null;

        if (strstr(strtolower(php_uname("s")), 'windows')) {
            $pids = array_column(array_map('str_getcsv', explode("\n",trim(`tasklist /FO csv /NH`))), 1);
        } else {
            $pids = explode(PHP_EOL, `ps -e | awk '{print $1}'`);
        }

        if (in_array( self::$pid, $pids ))
            return TRUE;
        return FALSE;
    }

    public static function lock()
    {
        global $argv;

        $lock_file = LOCK_DIR . $argv[0] . LOCK_SUFFIX;

        if (file_exists( $lock_file )) {
            // Is running?
            self::$pid = file_get_contents( $lock_file );
            if (self::isRunning()) {
                Log::write( "==" . self::$pid . "== Already in progress..." );
                return FALSE;
            } else {
                Log::write( "==" . self::$pid . "== Previous job died abruptly..." );
            }
        }

        self::$pid = getmypid();
        file_put_contents( $lock_file, self::$pid );
        Log::write( "==" . self::$pid . "== Lock acquired, processing the job..." );
        return self::$pid;
    }

    public static function unlock()
    {
        global $argv;

        $lock_file = LOCK_DIR . $argv[0] . LOCK_SUFFIX;

        if (file_exists( $lock_file ))
            unlink( $lock_file );

        Log::write( "==" . self::$pid . "== Releasing lock..." );
        return TRUE;
    }

}
