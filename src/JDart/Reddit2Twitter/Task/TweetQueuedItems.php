<?php

namespace JDart\Reddit2Twitter\Task;

class TweetQueuedItems
{
	protected $em;
	protected $tweeter;

	public function __construct(\Doctrine\ORM\EntityManager $em, \JDart\Reddit2Twitter\Task\CreateTweetFromRedditLink $tweeter)
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

			if ($this->tweeter->tweet()) {

				$rp->setPosted(true);
				$rp->setQueued(false);

				$this->em->persist($rp);
				$this->em->flush();
			}

			sleep(5);
		}		
	}
}
