<?php

/**
 * @package    com.braincrafted.4sqtokml
 * @subpackage Generator
 * @category   tests
 * @author     Florian Eckerstorfer <florian@eckerstorfer.co>
 * @copyright  2012 Florian Eckerstorfer
 * @license    http://opensource.org/licenses/MIT The MIT License
 */

namespace FoursquareToKml\Tests\Generator;

use FoursquareToKml\Generator\KmlGenerator;

/**
 * KmlGeneratorTest
 *
 * @package    com.braincrafted.4sqtokml
 * @subpackage Generator
 * @category   tests
 * @author     Florian Eckerstorfer <florian@eckerstorfer.co>
 * @copyright  2012 Florian Eckerstorfer
 * @license    http://opensource.org/licenses/MIT The MIT License
 */
class KmlGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerate()
    {
        $checkin1 = new \stdClass();
        $checkin1->venue = new \stdClass();
        $checkin1->venue->name = 'Location 1';
        $checkin1->venue->location = new \stdClass();
        $checkin1->venue->location->lng = 16.370090246201;
        $checkin1->venue->location->lat = 48.195081669619;

        $checkin2 = new \stdClass();
        $checkin2->venue = new \stdClass();
        $checkin2->venue->name = 'Location 2';
        $checkin2->venue->location = new \stdClass();
        $checkin2->venue->location->lng = 16.298954330274;
        $checkin2->venue->location->lat = 48.187021372254;
        $checkins = array($checkin1, $checkin2);
        $generator = new KmlGenerator();
        $result = $generator->generate($checkins);

        $this->assertContains('<name><![CDATA[foursquare checkin history]]></name>', $result);
        $this->assertContains('<name><![CDATA[Location 1]]></name>', $result);
        $this->assertContains('<extrude><![CDATA[1]]></extrude>', $result);
        $this->assertContains('<altitudeMode><![CDATA[relativeToGround]]></altitudeMode>', $result);
        $this->assertContains('<coordinates><![CDATA[16.370090246201,48.195081669619]]></coordinates>', $result);
        $this->assertContains('<name><![CDATA[Location 2]]></name>', $result);
        $this->assertContains('<coordinates><![CDATA[16.298954330274,48.187021372254]]></coordinates>', $result);
    }
}
