<?php

/**
 * @package    com.braincrafted.4sqtokml
 * @subpackage Generator
 * @category   library
 * @author     Florian Eckerstorfer <florian@eckerstorfer.co>
 * @copyright  2012 Florian Eckerstorfer
 * @license    http://opensource.org/licenses/MIT The MIT License
 */

namespace FoursquareToKml\Generator;

/**
 * KmlGenerator
 *
 * @package    com.braincrafted.4sqtokml
 * @subpackage Generator
 * @category   library
 * @author     Florian Eckerstorfer <florian@eckerstorfer.co>
 * @copyright  2012 Florian Eckerstorfer
 * @license    http://opensource.org/licenses/MIT The MIT License
 */
class KmlGenerator implements GeneratorInterface
{
    /**
     * @see parent::generate
     */
    public function generate(array $checkins)
    {
        $document = new \kml_Folder('foursquare checkin history');
        foreach ($checkins as $checkin) {
            if (isset($checkin->venue)) {
                $placemark = new \kml_Placemark($checkin->venue->name);
                $point = new \kml_Point($checkin->venue->location->lng, $checkin->venue->location->lat);
                $point->set_altitudeMode('relativeToGround');
                $point->set_extrude(1);
                $placemark->set_Geometry($point);
                $document->add_Feature($placemark);
            }
        }

        // Generate KML XML and save to disk
        ob_start();
        $document->dump(false);
        $kml = ob_get_contents();
        ob_end_clean();

        return $kml;
    }
}
