<?php

namespace App\Helpers;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Linux Helper
 *
 * https://symfony.com/doc/current/components/process.html
 * https://linuxize.com/post/how-to-create-users-in-linux-using-the-useradd-command/
 */
class Linux
{
    /**
     * Get server uptime and return formated for humans
     *
     * @return string formated server uptime
     */
    public static function getUptime(): string
    {
        $str = @file_get_contents('/proc/uptime');
        $num = intval($str);
        $secs = fmod($num, 60);
        $num = intdiv($num, 60);
        $mins = $num % 60;
        $num = intdiv($num, 60);
        $hours = $num % 24;
        $num = intdiv($num, 24);
        $days = $num;

        return sprintf('%03dd: %02dh: %02dm: %02ds', $days, $hours, $mins, $secs);
    }

    /**
     * Add Linux user to the system
     *
     * @param  string  $username  linux username
     */
    public static function userAdd(string $username = ''): string
    {
        if (! empty($username)) {
            // -m generate home directory
            $process = new Process(['useradd', '-m', $username]);
            $process->run();

            // executes after the command finishes
            if (! $process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $output = $process->getOutput();

            return $output;
        }

        return 'ERROR: username can\'t be empty';
    }
}
