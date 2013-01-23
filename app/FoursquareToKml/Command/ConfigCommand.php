<?php

namespace FoursquareToKml\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Yaml;

use FoursquareToKml\Util;

class ConfigCommand extends Command
{
    /** @var string */
    private $configFilename;

    protected function configure()
    {
        $this->configFilename = Util::getHomeDirectory() . '/.4sqtokml.yml';
        $this->setName('config')
             ->setDescription('Configure 4sqtokml')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelperSet()->get('dialog');

        // Default configuration
        $config = array(
            'foursquare' => array(
                'endpoint_uri'          => 'https://api.foursquare.com',
                'api_version'           => 2,
                'authorize_uri'         => 'https://foursquare.com/oauth2/authorize',
                'access_token_uri'      => 'https://foursquare.com/oauth2/access_token',
                'callback_uri'          => 'http://4sqtokml.braincrafted.com/'
            )
        );

        if (file_exists($this->configFilename)) {
            $config = array_merge($config, Yaml::parse($this->configFilename));
        }

        $clientId = null;
        while (!$clientId) {
            $clientId = $dialog->ask(
                $output,
                sprintf('Please enter your <info>Client ID</info>%s: ', (isset($config['foursquare']['client_id']) ? ' [' . $config['foursquare']['client_id'] . ']' : '')),
                isset($config['foursquare']['client_id']) ? $config['foursquare']['client_id'] : null
            );
        }
        $config['foursquare']['client_id'] = $clientId;

        $clientSecret = null;
        while (!$clientSecret) {
            $clientSecret = $dialog->ask(
                $output,
                sprintf('Please enter your <info>Client Secret</info>%s: ', (isset($config['foursquare']['client_secret']) ? ' [' . $config['foursquare']['client_secret'] . ']' : '')),
                isset($config['foursquare']['client_secret']) ? $config['foursquare']['client_secret'] : null
            );
        }
        $config['foursquare']['client_secret'] = $clientSecret;

        $callbackUri = null;
        while (!$callbackUri) {
            $callbackUri = $dialog->ask(
                $output,
                sprintf('Please enter the <info>Callback URI</info>%s: ', (isset($config['foursquare']['callback_uri']) ? ' [' . $config['foursquare']['callback_uri'] . ']' : '')),
                isset($config['foursquare']['callback_uri']) ? $config['foursquare']['callback_uri'] : null
            );
        }
        $config['foursquare']['callback_uri'] = $callbackUri;

        file_put_contents($this->configFilename, Yaml::dump($config));
        $output->writeln(sprintf('Saved configuration to <info>%s</info>.', $this->configFilename));
    }
}
