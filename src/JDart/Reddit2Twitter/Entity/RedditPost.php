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
}
