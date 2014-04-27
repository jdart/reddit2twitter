<?php

error_reporting(E_ALL ^ E_DEPRECATED);

$base_dir = realpath(__DIR__ . "/../../../../../../");

chdir($base_dir);

require_once "vendor/autoload.php";

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

$container = new ContainerBuilder();
$container->setParameter('paths.root', $base_dir.'/');
$container->setParameter('paths.app', $base_dir.'/app');

$global_config_loader = new YamlFileLoader($container, new FileLocator(__DIR__));
$global_config_loader->load('services.yml');

$local_config_loader = new YamlFileLoader($container, new FileLocator($base_dir . "/app/config"));
$local_config_loader->load('config.yml');

$cli_application = $container->get('cli_application');
$cli_application->setCatchExceptions(true);
$cli_application->setHelperSet(ConsoleRunner::createHelperSet($container->get('entity_manager')));
ConsoleRunner::addCommands($cli_application);

$cli_application->run();

