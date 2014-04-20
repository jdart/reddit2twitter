<?php

namespace JDart\Reddit2Twitter\Setup;

class ClientFactory
{
	public static function getAuthorizedRedditClient(\Reddit\Api\Client\Factory $factory, array $config)
	{
		static $reddit_client;

		if ( ! isset($reddit_client)) {

			$reddit_client = $factory->createClient();

			$reddit_client
				->getCommand('Login', $config)
				->execute();
		}

		return $reddit_client;
	}
}