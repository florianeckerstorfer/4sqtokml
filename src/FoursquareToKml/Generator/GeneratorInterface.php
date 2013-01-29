<?php

/**
 * @package    com.braincrafted.4sqtokml
 * @subpackage Generator
 * @category   interface
 * @author     Florian Eckerstorfer <florian@eckerstorfer.co>
 * @copyright  2012 Florian Eckerstorfer
 * @license    http://opensource.org/licenses/MIT The MIT License
 */


namespace FoursquareToKml\Generator;

/**
 * GeneratorInterface
 *
 * @package    com.braincrafted.4sqtokml
 * @subpackage Generator
 * @category   interface
 * @author     Florian Eckerstorfer <florian@eckerstorfer.co>
 * @copyright  2012 Florian Eckerstorfer
 * @license    http://opensource.org/licenses/MIT The MIT License
 */
interface GeneratorInterface
{
    /**
     * Generates KML.
     *
     * @param array $checkins The array of checkins
     *
     * @return string KML
     */
    public function generate(array $checkins);
}
