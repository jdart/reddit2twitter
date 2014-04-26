<?php

namespace JDart\Reddit2Twitter\Task;

use Doctrine\ORM\EntityManager;
use JDart\Reddit2Twitter\Reddit\ClientInterface;
use JDart\Reddit2Twitter\Entity\RedditPost;
use Reddit\Thing\Link;

class QueueItems
{
	protected $em;
	protected $rc;
	protected $threshold;

	public function __construct(EntityManager $em, ClientInterface $rc)
	{
		$this->em = $em;
		$this->rc = $rc;
	}

	public function setThreshold($threshold)
	{
		$this->threshold = $threshold;
	}

	public function execute()
	{
		$results = $this->rc->getLinksFromSubreddit('all');

		foreach ($results as $link) {

			$rp = $this->findOrCreateLocalRedditPost($link);

			if ($rp->getPosted() || $rp->getQueued())
				continue;

			if ($rp->getScore() >= $this->threshold) {

				$rp->setPostData(serialize($link));
				$rp->setQueued(true);
				$this->em->persist($rp);
			}
		}

		$this->em->flush();
	}

	protected function findOrCreateLocalRedditPost(Link $link)
	{
		static $findQuery;

		if ( ! isset($findQuery)) {
			$findQuery = $this->em
				->createQueryBuilder()
				->select('p')
				->from('JDart\Reddit2Twitter\Entity\RedditPost', 'p')
				->where('p.reddit_id = ?1')
				->getQuery();
		}

		$rp = $findQuery
			->setParameter(1, $link->id)
			->getOneOrNullResult();

		if (is_null($rp)) {

			$rp = new RedditPost;
			$rp->setRedditId($link->id);
		}

		$rp->setScore($link->ups);

		$this->em->persist($rp);

		return $rp;
	}
}