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
    /** @var array */
    protected $config;

    /** @var string */
    protected $token;

    /**
     * Constructor.
     *
     * @param array  $config The config array
     */
    public function __construct(array $config)
    {
        $this->config   = $config;
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
        $factory = $this->getGatewayFactory();
        $authGateway = $factory->getAuthenticationGateway(
            $this->config['foursquare']['client_id'],
            $this->config['foursquare']['client_secret'],
            $this->config['foursquare']['authorize_uri'],
            $this->config['foursquare']['access_token_uri'],
            $this->config['foursquare']['callback_uri']
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
    protected function getCheckins()
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
        $factory = $this->getGatewayFactory();
        $factory->setToken($this->token);
        return $factory->getUsersGateway();
    }

    /**
     * Returns the gateway factory.
     *
     * @return ApiGatewayFactory
     */
    protected function getGatewayFactory()
    {
        $client = new SymfonyHttpClient();
        $factory = new ApiGatewayFactory($client);
        $factory->setEndpointUri($this->config['foursquare']['endpoint_uri']);
        $factory->useVersion($this->config['foursquare']['api_version']);

        return $factory;
    }
}
