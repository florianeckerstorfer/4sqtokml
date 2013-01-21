<?php

namespace FoursquareToKml\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Yaml;

use TheTwelve\Foursquare\HttpClient\SymfonyHttpClient;
use TheTwelve\Foursquare\ApiGatewayFactory;
use TheTwelve\Foursquare\AuthenticationGateway;

require __DIR__.'/../../../vendor/php-kml/php-kml/lib/kml.php';

class ExportCommand extends Command
{
    /** @var array */
    protected $config;

    /** @var AuthenticationGateway */
    protected $authGateway;

    /** @var ApiGatewayFactory */
    protected $gatewayFactory;

    protected function configure()
    {
        $this->setName('export')
             ->setDescription('Export your Foursquare checkins to KML')
             ->addArgument('output-file', InputArgument::OPTIONAL, 'File to save KML to', 'checkins.xml')
             ->addArgument('checkin-limit', InputArgument::OPTIONAL, 'Maximum number of checkins', 0)
        ;

        $this->config = Yaml::parse('./config/foursquare.yml');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $checkinLimit = $input->getArgument('checkin-limit');

        $gateway = $this->getUserGateway($output);
        try {
            $user = $gateway->getUser();
        } catch (\RuntimeException $e) {
            // Ok. Reset oAuth token and try again.
            $this->config['foursquare']['oauth_token'] = null;
            $gateway = $this->getUserGateway($output);
            try {
                $user = $gateway->getUser();
            } catch (\RuntimeException $e) {
                $output->writeln('<error>There was an error and I could not authenticate you correctly. I\'m sorry.</error>');
            }
        }

        if ($checkinLimit !== 0 && $checkinLimit < $user->checkins->count) {
            $realCheckinLimit = $checkinLimit;
        } else {
            $realCheckinLimit = $user->checkins->count;
        }

        $output->writeln(sprintf(
            'You have <info>%d</info> checkins. I\'m going to fetch the last <info>%d</info>.',
            $user->checkins->count,
            $realCheckinLimit
        ));
        if ($realCheckinLimit > 1000) {
            $output->writeln('<comment>Get a coffee, this may take a while.</comment>');
        }

        $output->writeln("");

        try {
            $checkins = $this->getCheckins($output, $gateway, $realCheckinLimit, $input->getArgument('checkin-limit'));
        } catch (\RuntimeException $e) {
            $output->writeln('<error>There was an error and I could not retrieve the checkins for you. I am so sorry.</error>');
        }

        $output->writeln(sprintf('Downloaded <info>%d</info> Foursquare checkins', count($checkins)));

        // Generate KML
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
        file_put_contents($input->getArgument('output-file'), $kml);

        $output->writeln(sprintf('Saved checkins to <info>%s</info>.', $input->getArgument('output-file')));
    }

    protected function getCheckins(OutputInterface $output, $gateway, $totalCheckins, $limit = null)
    {
        $progress = $this->getHelperSet()->get('progress');

        $checkins   = array();
        $offset     = 0;
        $pageSize   = 250;

        $progress->start($output, $totalCheckins);

        while (true) {
            if (0 === $limit || $limit > $pageSize) {
                $pageLimit = $pageSize;
            } else {
                $pageLimit = $limit;
            }
            $checkinPage = $gateway->getCheckins(array('offset' => $offset, 'limit' => $pageLimit));
            $progress->advance(count($checkinPage));
            if (0 === count($checkinPage)) {
                break;
            }

            $checkins = array_merge($checkins, $checkinPage);
            $offset += $pageSize;

            if ($limit > 0 && count($checkins) >= $limit) {
                break;
            }
        }

        $progress->finish();

        return $checkins;
    }

    protected function getCode(OutputInterface $output)
    {
        $output->writeln("Copy the following URL to your browser and copy the code.");
        $output->writeln($this->getAuthenticationGateway()->getLoginUri());

        $dialog = $this->getHelperSet()->get('dialog');
        $code = $dialog->ask(
            $output,
            'Please paste the code returned by the above URL: ',
            null
        );

        if (!$code) {
            $output->writeln("<error>Please paste the code returned after the authentication.");
            return;
        }


        return $code;
    }

    protected function getToken($code)
    {
        $this->config['foursquare']['oauth_token'] = $this->getAuthenticationGateway()->authenticateUser($code);
        file_put_contents('./config/foursquare.yml', Yaml::dump($config));
        return $config;
    }

    protected function getUserGateway(OutputInterface $output)
    {
        $authGateway = $this->getAuthenticationGateway();
        if (!isset($this->config['foursquare']['oauth_token']) || !$this->config['foursquare']['oauth_token']) {
            $output->writeln("<error>Couldn't find valid oAuth token. Starting authentication.");
            $code = $this->getCode($output);
            $token = $this->getToken($code);
        } else {
            $token = $this->config['foursquare']['oauth_token'];
        }

        $this->getGatewayFactory()->setToken($token);
        $gateway = $this->getGatewayFactory()->getUsersGateway();

        return $gateway;
    }

    protected function getGatewayFactory()
    {
        if (!$this->gatewayFactory) {
            $client = new SymfonyHttpClient();
            $this->gatewayFactory = new ApiGatewayFactory($client);
            $this->gatewayFactory->setEndpointUri($this->config['foursquare']['endpoint_uri']);
            $this->gatewayFactory->useVersion($this->config['foursquare']['api_version']);
        }

        return $this->gatewayFactory;
    }

    protected function getAuthenticationGateway()
    {
        if (!$this->authGateway) {
            $this->authGateway = $this->getGatewayFactory()->getAuthenticationGateway(
                $this->config['foursquare']['client_id'],
                $this->config['foursquare']['client_secret'],
                $this->config['foursquare']['authorize_uri'],
                $this->config['foursquare']['access_token_uri'],
                $this->config['foursquare']['callback_uri']
            );
        }

        return $this->authGateway;
    }
}
