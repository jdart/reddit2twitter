<?php

namespace JDart\Reddit2Twitter\Command;

use JDart\Reddit2Twitter\Twitter\CreateTweetFromRedditPost;
use JDart\Reddit2Twitter\Entity\RedditPost;
use JDart\Reddit2Twitter\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CronCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this
			->setName('r2t:update')
			->setDescription('Run Reddit2Twitter');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$qi = $this->getContainer()->get('r2t_task.queue_items');
		$qi->execute();

		$qi = $this->getContainer()->get('r2t_task.tweet_queued_items');
		$qi->execute();
	}		
}