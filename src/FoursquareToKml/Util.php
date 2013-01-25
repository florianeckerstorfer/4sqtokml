<?php

/**
 * @package    com.braincrafted.4sqtokml
 * @subpackage FoursquareToKml
 * @category   library
 * @author     Florian Eckerstorfer <florian@eckerstorfer.co>
 * @copyright  2012 Florian Eckerstorfer
 * @license    http://opensource.org/licenses/MIT The MIT License
 */

namespace FoursquareToKml;

/**
 * Util
 *
 * @package    com.braincrafted.4sqtokml
 * @subpackage FoursquareToKml
 * @category   library
 * @author     Florian Eckerstorfer <florian@eckerstorfer.co>
 * @copyright  2012 Florian Eckerstorfer
 * @license    http://opensource.org/licenses/MIT The MIT License
 */
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
