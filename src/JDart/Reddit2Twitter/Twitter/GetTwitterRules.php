<?php

namespace JDart\Reddit2Twitter\Twitter;

class GetTwitterRules
{
	protected $twitterOauth;
	protected $cacheDir;
	protected $cacheLifetime;

	public function __construct($cacheDir, $cacheLifetime, \tmhOAuth $twitterOauth)
	{
		$this->cacheDir = $cacheDir;
		$this->cacheLifetime = $cacheLifetime;
		$this->twitterOauth = $twitterOauth;
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

			$this->twitterOauth
				->request(
					'GET',
					$this->twitterOauth->url('https://api.twitter.com/1.1/help/configuration.json')
				);

			$config = json_decode($this->twitterOauth->response['response']);

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
