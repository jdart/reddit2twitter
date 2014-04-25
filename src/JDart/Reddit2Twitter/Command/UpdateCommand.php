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

class UpdateCommand extends ContainerAwareCommand
{
	const SCORE_THRESHOLD = 2000;

	protected function configure()
	{
		$this
			->setName('r2t:update')
			->setDescription('Run Reddit2Twitter');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$rc = $this->getContainer()->get('reddit_client');
		$em = $this->getContainer()->get('entity_manager');

		$results = $rc
			->getCommand('GetLinksBySubreddit', array('subreddit' => 'all'))
			->execute();

		foreach ($results as $result) {

			$rp = $this->findOrCreateRedditPost($result->id);

			if ($rp->getPosted())
				continue;

			$rp->setScore($result->ups);
			$em->persist($rp);

			if ($rp->getScore() >= self::SCORE_THRESHOLD) {

				$tweet = new CreateTweetFromRedditPost(
					$result, 
					$this->getContainer()->get('twitter_client'),
					$this->getContainer()->get('twitter_rules')
				);
				
				$tweet->execute();
			}
		}

		$em->flush();
	}

	protected function findOrCreateRedditPost($reddit_id)
	{
		static $findQuery;

		if ( ! isset($findQuery)) {
			$findQuery = $this->getContainer()
			->get('entity_manager')
			->createQueryBuilder()
			->select('p')
			->from('JDart\Reddit2Twitter\Entity\RedditPost', 'p')
			->where('p.reddit_id = ?1')
			->getQuery();
		}

		try {

			$rp = $findQuery
				->setParameter(1, $reddit_id)
				->getSingleResult();

		} catch (\Doctrine\ORM\NoResultException $e) {

			$rp = new RedditPost;
			$rp->setRedditId($reddit_id);
		}

		return $rp;
	}
}