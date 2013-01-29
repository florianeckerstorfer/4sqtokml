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

use \Pimple;

use TheTwelve\Foursquare\HttpClient\SymfonyHttpClient;
use TheTwelve\Foursquare\ApiGatewayFactory;
use TheTwelve\Foursquare\AuthenticationGateway;

/**
 * FoursquareToKml
 *
 * @package    com.braincrafted.4sqtokml
 * @subpackage FoursquareToKml
 * @category   library
 * @author     Florian Eckerstorfer <florian@eckerstorfer.co>
 * @copyright  2012 Florian Eckerstorfer
 * @license    http://opensource.org/licenses/MIT The MIT License
 */
class FoursquareToKml
{
    /** @var Pimple */
    protected $container;

    /** @var string */
    protected $token;

    /**
     * Constructor.
     *
     * @param Pimple $container The service container
     */
    public function __construct(Pimple $container)
    {
        $this->container = $container;
    }

    /**
     * Sets the oAuth token.
     *
     * @param string $token The oAuth token
     *
     * @return FoursquareToKml
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Returns the oAuth token.
     *
     * @return string The oAuth token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Returns if the oAuth token is set.
     *
     * @return bool TRUE if the oAuth token is set, FALSE otherwise
     */
    public function hasToken()
    {
        return null !== $this->token;
    }

    /**
     * Returns the Foursquare authentication gateway.
     *
     * @return AuthenticationGateway
     */
    public function getAuthGateway()
    {
        $factory = $this->container['gateway_factory'];
        $authGateway = $factory->getAuthenticationGateway(
            $this->container['config']['foursquare']['client_id'],
            $this->container['config']['foursquare']['client_secret'],
            $this->container['config']['foursquare']['authorize_uri'],
            $this->container['config']['foursquare']['access_token_uri'],
            $this->container['config']['foursquare']['callback_uri']
        );

        return $authGateway;
    }

    /**
     * Returns the currently logged in user.
     *
     * @return \stdClass The user object
     */
    public function getUser()
    {
        return $this->getUserGateway()->getUser();
    }

    /**
     * Generates the KML for the given checkins.
     *
     * @param array $checkins
     *
     * @return string
     */
    public function generateKml()
    {
        $checkins = $this->getCheckins();

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

    /**
     * Returns the checkins.
     *
     * @return array
     */
    public function getCheckins()
    {
        $checkins   = array();
        $offset     = 0;
        $pageSize   = 250;

        $gateway = $this->getUserGateway();

        while (true) {
            $checkinPage = $gateway->getCheckins(array('offset' => $offset, 'limit' => $pageSize));
            if (0 === count($checkinPage)) {
                break;
            }

            $checkins = array_merge($checkins, $checkinPage);
            $offset += $pageSize;
        }

        return $checkins;
    }

    /**
     * Returns the user gateway.
     *
     * @return TheTwelve\Foursquare\UsersGateway
     */
    public function getUserGateway()
    {
        if (!$this->hasToken()) {
            throw new NoOAuthTokenException('Can\'t initialize user gateway because no oAuth token is specified.');
        }
        $factory = $this->container['gateway_factory'];
        $factory->setToken($this->token);
        return $factory->getUsersGateway();
    }
}
