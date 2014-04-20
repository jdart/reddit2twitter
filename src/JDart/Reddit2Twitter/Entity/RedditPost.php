<?php

namespace JDart\Reddit2Twitter\Entity;

/**
 * @Entity @Table(name="reddit_post")
 **/
class RedditPost
{
	/** @Id @Column(type="integer") @GeneratedValue **/
	protected $id;

	/** @Column(type="string", length=6) **/
	protected $reddit_id;

	/** @Column(type="integer") **/
	protected $score;

	/** @Column(type="boolean") **/
	protected $posted = false;

	public function setId($id)
	{
		$this->id = (int)$id;
	}

	public function getId()
	{
		return $this->id;
	}

	public function setRedditId($id)
	{
		$this->reddit_id = (string)$id;
	}

	public function getRedditId()
	{
		return $this->reddit_id;
	}

	public function setScore($score)
	{
		$this->score = (int)$score;
	}

	public function getScore()
	{
		return $this->score;
	}

	public function setPosted($posted)
	{
		$this->posted = (bool)$posted;
	}

	public function getPosted()
	{
		return $this->posted;
	}
}
