<?php

require_once __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;

use FoursquareToKml\Command\ExportCommand;

$console = new Application();
$console->add(new ExportCommand());
$console->run();
