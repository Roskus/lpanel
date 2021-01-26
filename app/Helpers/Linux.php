<?php
namespace App\Helpers;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Linux Helper
 *
 * https://symfony.com/doc/current/components/process.html
 * https://linuxize.com/post/how-to-create-users-in-linux-using-the-useradd-command/
 *
 */
class Linux
{

    public static function getUptime()
    {
        $str   = @file_get_contents('/proc/uptime');
        $num   = floatval($str);
        $secs  = fmod($num, 60); $num = intdiv($num, 60);
        $mins  = $num % 60;      $num = intdiv($num, 60);
        $hours = $num % 24;      $num = intdiv($num, 24);
        $days  = $num;
        return sprintf("%03d:%02d:%02d:%02d", $days, $hours, $mins, $secs);
    }

    /**
     * Add Linux user to the system
     *
     * @param string $username linux username
     *
     *
     */
    public static function userAdd(string $username = '')
    {
        if (!empty($username)) {
            // -m generate home directory
            $process = new Process(['useradd', '-m', $username]);
            $process->run();

            // executes after the command finishes
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            echo $process->getOutput();

        }
    }
}
