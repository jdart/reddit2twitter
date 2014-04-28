<?php

error_reporting(E_ALL ^ E_DEPRECATED);

$base_dir = realpath(__DIR__ . "/../../../../../../");

chdir($base_dir);

system('php vendor/phpunit/phpunit/phpunit vendor/jdart/reddit2twitter/src/JDart/Reddit2Twitter/Tests/');
