<?php

namespace FoursquareToKml;

class Util
{
    /**
     * Returns the users home directory.
     *
     * @return string The path to the users home directory.
     */
    public static function getHomeDirectory()
    {
        if (isset($_SERVER['HOME'])) {
            return $_SERVER['HOME'];
        } elseif (isset($_SERVER['HOMEDRIVE']) && isset($_SERVER['HOMEPATH'])) {
            return $_SERVER['HOMEDRIVE'] . '/' . $_SERVER['HOMEPATH'];
        } else {
            return '.';
        }
    }
}
