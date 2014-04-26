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

	/** @Column(type="boolean") **/
	protected $queued = false;

	/** @Column(type="text", nullable=true) **/
	protected $post_data = null;

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

	public function setQueued($queued)
	{
		$this->queued = $queued;
	}

	public function getQueued()
	{
		return $this->queued;
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

	public function getPostData()
	{
		return $this->post_data;
	}

	public function setPostData($post_data)
	{
		$this->post_data = $post_data;
	}
}
