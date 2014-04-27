<?php

namespace JDart\Reddit2Twitter\Task;

use Doctrine\ORM\EntityManager;
use JDart\Reddit2Twitter\Task\TweetItem;
use JDart\Reddit2Twitter\Exception\TweetRejectedException;
use JDart\Reddit2Twitter\Exception\InvalidMediaException;

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
			->where('p.posted = false')
			->orderBy('p.score', 'DESC')
			->getQuery()
			->getResult();
	}

	public function execute()
	{
		foreach ($this->getQueued() as $rp) {

			$this->tweeter->setLink(unserialize($rp->getPostData()));

			try {
				$this->tweeter->tweet();
			} catch (TweetRejectedException $e) {
				continue;
			} catch (InvalidMediaException $e) {
				continue;
			}

			$rp->setPosted(true);
			$this->em->persist($rp);
			$this->em->flush();

			$this->tweeter->cleanUp();
			$this->tweeter->checkForLimitExceeded();

			break;
		}		
	}
}
