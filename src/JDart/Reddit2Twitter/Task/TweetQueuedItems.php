<?php

namespace JDart\Reddit2Twitter\Task;

use Doctrine\ORM\EntityManager;
use JDart\Reddit2Twitter\Task\TweetItem;
use JDart\Reddit2Twitter\Exception\LimitExceededException;
use JDart\Reddit2Twitter\Exception\TweetFailedException;

class TweetQueuedItems
{
	protected $em;
	protected $tweeter;

	public function __construct(EntityManager $em, TweetItem $tweeter)
	{
		$this->em = $em;
		$this->tweeter = $tweeter;
	}

	public function getQueued()
	{
		return $this->em
			->createQueryBuilder()
			->select('p')
			->from('JDart\Reddit2Twitter\Entity\RedditPost', 'p')
			->where('p.queued = true')
			->getQuery()
			->getResult();
	}

	public function execute()
	{
		foreach ($this->getQueued() as $rp) {

			$link = unserialize($rp->getPostData());

			$this->tweeter->setLink($link);

			try {

				$this->tweeter->tweet();

			} catch (TweetFailedException $e) {

				continue;
			}

			$rp->setPosted(true);
			$rp->setQueued(false);

			$this->em->persist($rp);
			$this->em->flush();

			$window = $this->tweeter->getNextWindow();

			if ($window !== 0) {
				$e = new LimitExceededException;
				$e->setNextWindow($window);
				throw $e;
			}

			sleep(5);
		}		
	}
}
