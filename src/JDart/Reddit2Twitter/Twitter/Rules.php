<?php

namespace JDart\Reddit2Twitter\Twitter;

use \JDart\Reddit2Twitter\Twitter\ClientInterface;

class Rules
{
	const MAX_TWEET_LENGTH = 140;

	protected $twitterClient;
	protected $cacheDir;
	protected $cacheLifetime;

	public function __construct($cacheDir, $cacheLifetime, ClientInterface $twitterClient)
	{
		$this->cacheDir = $cacheDir;
		$this->cacheLifetime = $cacheLifetime;
		$this->twitterClient = $twitterClient;
	}

	protected function getAllRules()
	{
		$cacheDir = __DIR__ . "/../../../../../../../" . $this->cacheDir;

		$cacheFile = $cacheDir . '/twitter_rules';

		if (
			is_file($cacheFile) 
			&& filemtime($cacheFile) > strtotime($this->cacheLifetime)
		) {

			$config = unserialize(file_get_contents($cacheFile));

		} else {

			$this->twitterClient
				->getRequest('https://api.twitter.com/1.1/help/configuration.json');

			$config = json_decode($this->twitterClient->getResponseBody());

			file_put_contents($cacheFile, serialize($config));
		}

		return $config;
	}

	public function getRule($key)
	{
		static $rules;

		if ( ! isset($rules))
			$rules = $this->getAllRules();

		return $rules->$key;
	}

	public function getCharactersReservedPerMedia()
	{
		return $this->getRule('characters_reserved_per_media');
	}

	public function getShortUrlLength()
	{
		return $this->getRule('short_url_length');
	}

	public function getShortUrlLengthHttps()
	{
		return $this->getRule('short_url_length_https');
	}

	public function getMaxPhotoSize()
	{
		return $this->getRule('photo_size_limit');
	}

	public function getMaxLength()
	{
		return self::MAX_TWEET_LENGTH;
	}
}
