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
use JDart\Reddit2Twitter\Exception\LimitExceededException;

class CronCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this
			->setName('r2t:cron')
			->setDescription('Run Reddit2Twitter');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$appDir = realpath(__DIR__ . "/../../../../../../../") . '/';
		$lockFile = $appDir . $this->getContainer()->getParameter('lock.file');
		$nextWindowFile = $appDir . $this->getContainer()->getParameter('lock.next_window');

		if (is_file($lockFile)) {

			$output->writeln('Lock file found.');
			$pid = file_get_contents($lockFile);

			if (file_exists('/proc/'.$pid)) {
				$output->writeln('Previous job still running, not running.');
				return;
			}

			$output->writeln('Previous job not running, continuing.');
		}

		if (is_file($nextWindowFile)) {

			$nextWindow = file_get_contents($nextWindowFile);
			if (strtotime($nextWindow) < time()) {
				$output->writeln('Still waiting for next window.');
				return;
			}
			
			unlink($nextWindowFile);
		}

		file_put_contents($lockFile, getmypid());

		$output->writeln('Queueing...');

		$qi = $this->getContainer()->get('r2t_task.queue_items');
		$qi->execute();

		$output->writeln('Tweeting...');

		try {

			$qi = $this->getContainer()->get('r2t_task.tweet_queued_items');
			$qi->execute();

		} catch (LimitExceededException $e) {

			$output->writeln('rate limit exceeded, waiting... ' . date('Y-m-d H:i:s', strtotime($e->getNextWindow())));

			file_put_contents($nextWindowFile, $e->getNextWindow());
		}

		unlink($lockFile);

		$output->writeln('Done.');
	}		
}