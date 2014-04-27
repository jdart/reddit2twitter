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

	public function garbageCollectDb()
	{
		$result = $this->em
			->createQueryBuilder()
			->delete('\JDart\Reddit2Twitter\Entity\RedditPost', 'p')
			->where('p.posted = false')
			->getQuery()
			->getResult();
	}

	public function execute()
	{
		$this->garbageCollectDb();

		$results = $this->rc->getLinksFromSubreddit('all');

		foreach ($results as $link) 
			$this->findOrCreateLocalRedditPost($link);

		$this->em->flush();
	}

	protected function findOrCreateLocalRedditPost(Link $link)
	{
		static $findQuery;

		if ( ! isset($findQuery)) {
			$findQuery = $this->em
				->createQueryBuilder()
				->select('p')
				->from('\JDart\Reddit2Twitter\Entity\RedditPost', 'p')
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
		$rp->setPostData(serialize($link));

		$this->em->persist($rp);

		return $rp;
	}
}