<?php

namespace App\Helpers;

/**
 * File Helper
 */
class File
{
    /**
     * Return human formated file size
     *
     * @param  int  $size  bytes
     * @return string formated size for humans
     */
    public static function formatSize($size)
    {
        $suffix = '';
        switch ($size) {
            case $size > 1099511627776:
                $size /= 1099511627776;
                $suffix = 'TB';
                break;
            case $size > 1073741824:
                $size /= 1073741824;
                $suffix = 'GB';
                break;
            case $size > 1048576:
                $size /= 1048576;
                $suffix = 'MB';
                break;
            case $size > 1024:
                $size /= 1024;
                $suffix = 'KB';
                break;
            default:
                $suffix = 'B';
        }

        return round($size, 2).' '.$suffix;
    }
}
