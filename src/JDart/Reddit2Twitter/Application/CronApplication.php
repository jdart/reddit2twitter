<?php

namespace JDart\Reddit2Twitter\Application;

use Symfony\Component\Console\Application;
use JDart\Reddit2Twitter\Command\CronCommand;

class CronApplication extends Application
{
	public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
    	parent::__construct($name, $version);

    	$this->add(new CronCommand);
    }
}