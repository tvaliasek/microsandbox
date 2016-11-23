<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

$configurator->setDebugMode(false); // enable for your remote IP

if(!is_dir(__DIR__ . '/../log')){
    mkdir(__DIR__ . '/../log', 0750);
}
if(!is_dir(__DIR__ . '/../temp')){
    mkdir(__DIR__ . '/../temp', 0750);
}

$configurator->enableDebugger(__DIR__ . '/../log');

$configurator->setTimeZone('Europe/Prague');
$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->register();

if(file_exists(__DIR__.'/config/config.database.neon')){
    $configurator->addConfig(__DIR__ . '/config/config.database.neon');
}

if(is_dir(__DIR__.'/components')){
    foreach(\Nette\Utils\Finder::findFiles('*config.neon')->from(__DIR__.'/components') as $path=>$splInfo){
        $configurator->addConfig($path);
    }
}
    
$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/pages.neon');

$container = $configurator->createContainer();

return $container;
