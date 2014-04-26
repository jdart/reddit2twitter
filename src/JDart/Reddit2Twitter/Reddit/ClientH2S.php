<?php

namespace JDart\Reddit2Twitter\Reddit;

use \JDart\Reddit2Twitter\Reddit\ClientInterface;
use \Reddit\Api\Client\Factory;

class ClientH2S implements ClientInterface
{
	protected $h2sClient;

	public function configure($config=array())
	{
		$factory = new Factory;

		$this->h2sClient = $factory->createClient();

		$this->h2sClient
			->getCommand('Login', $config)
			->execute();
	}

	public function getLinksFromSubreddit($subreddit='all')
	{
		return $this->h2sClient
			->getCommand('GetLinksBySubreddit', array('subreddit' => $subreddit))
			->execute();
	}
}

