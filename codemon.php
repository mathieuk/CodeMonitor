<?php

$autoloader = require 'vendor/autoload.php';
$autoloader->add('MAPIS\\', __DIR__ . '/src/');

use MAPIS\CodeMonitor;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new CodeMonitor\Command\CheckCommand());
$application->add(new CodeMonitor\Command\InitCommand());
$application->add(new CodeMonitor\Command\WatchCommand());
$application->run();