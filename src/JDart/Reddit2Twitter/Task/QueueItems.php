<?php

namespace JDart\Reddit2Twitter\Task;

class QueueItems
{
	protected $em;
	protected $rc;
	protected $threshold;

	public function __construct(\Doctrine\ORM\EntityManager $em, \Reddit\Api\Client $rc)
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
		$results = $this->rc
			->getCommand('GetLinksBySubreddit', array('subreddit' => 'all'))
			->execute();

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

	protected function findOrCreateLocalRedditPost(\Reddit\Thing\Link $link)
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

		try {

			$rp = $findQuery
				->setParameter(1, $link->id)
				->getSingleResult();

		} catch (\Doctrine\ORM\NoResultException $e) {

			$rp = new \JDart\Reddit2Twitter\Entity\RedditPost;
			$rp->setRedditId($link->id);
		}

		$rp->setScore($link->ups);

		$this->em->persist($rp);

		return $rp;
	}
}