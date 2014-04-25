<?php

namespace JDart\Reddit2Twitter\Twitter;

class GetTwitterRules
{
	protected $twitterApi;
	protected $cacheDir;
	protected $cacheLifetime;

	public function __construct($cacheDir, $cacheLifetime, \TwitterAPIExchange $twitterApi)
	{
		$this->cacheDir = $cacheDir;
		$this->cacheLifetime = $cacheLifetime;
		$this->twitterApi = $twitterApi;
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

			$config = $this->twitterApi
				->buildOauth('https://api.twitter.com/1.1/help/configuration.json', 'GET')
				->performRequest();

			$config = json_decode($config);

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
}
