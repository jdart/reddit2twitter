<?php

namespace JDart\Reddit2Twitter\Command;

use JDart\Reddit2Twitter\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this
			->setName('r2t:update')
			->setDescription('Run Reddit2Twitter');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('sup');
	}
}