<?php

require_once __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;

use FoursquareToKml\Command\ConfigCommand;
use FoursquareToKml\Command\ExportCommand;

$console = new Application();
$console->add(new ExportCommand());
$console->add(new ConfigCommand());
$console->run();
