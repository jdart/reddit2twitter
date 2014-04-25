<?php 

namespace JDart\Reddit2Twitter\Twitter;

class CreateTweetFromRedditPost
{
	protected $post;
	protected $twitterApi;
	protected $twitterRules;

	public function __construct(\Reddit\Thing\Link $post, \TwitterAPIExchange $twitterApi, GetTwitterRules $twitterRules)
	{
		$this->post = $post;
		$this->twitterApi = $twitterApi;
		$this->twitterRules = $twitterRules;
	}

	public function getPostfields()
	{
		$fields = array(
			'status' => $this->getPostTitle(),
		);

		if ($this->postIsMedia()) {

			$tmp_file = $this->downloadImage($this->getPostUrl());

			if ( ! $tmp_file)
				return false;

			$fields['media'] = array(file_get_contents($tmp_file));
		}

		return $fields;
	}

	public function downloadImage($url)
	{
		static $ch;
		static $finfo;

		if ( ! isset($ch))
			$ch = curl_init();

		if ( ! isset($finfo))
			$finfo = finfo_open(FILEINFO_MIME_TYPE);

		$tmp_file = '/tmp/' . md5($url);

		if (is_file($tmp_file)) {

			$tmp_file_handle = fopen($tmp_file, 'w');

			curl_setopt_array($ch, array(
				CURLOPT_FILE => $tmp_file_handle,
				CURLOPT_TIMEOUT => 30, 
				CURLOPT_URL => $url,
				CURLOPT_FOLLOWLOCATION => 1
			));

			curl_exec($ch);

			fclose($tmp_file_handle);

			if (curl_errno($ch))
				return false;
			
			$info = curl_getinfo($ch);

			if (empty($info['http_code']) || $info['http_code'] !== 200)
				return false;
		}

		$type = finfo_file($finfo, $tmp_file);

		if (in_array($type, array('image/jpeg', 'image/png'), true))
			return $tmp_file;
		
		return false;
	}

	public function getMediaPostfields()
	{
		$fields = $this->getPostfields();

		$tmp_file = $this->downloadImage($this->getPostUrl());

		if ($tmp_file) {
			$fields['media'] = array(file_get_contents($tmp_file));
			return $fields;
		}

		return false;
	}

	public function postIsMedia()
	{
		if ($url = $this->getPostUrl())
			return preg_match('/\.(jpg|jpeg|png)$/', $url);
		
		return false;
	}

	public function getPostUrl()
	{
		if (empty($this->post->url))
			return false;

		return $this->post->url;
	}

	public function getPostTitle()
	{
		$length = 140;
		$append = '';

		if ($url = $this->getPostUrl()) {

			if ($this->postIsMedia()) {
				$length = 140 - $this->twitterRules->getCharactersReservedPerMedia() - 1;
			} else {
				$length = 140 - $this->twitterRules->getShortUrlLength() - 1;
				$append = ' ' . $url;
			}
		}

		return substr($this->post->title, 0, $length) . $append;
	}

	public function execute()
	{
		$fields = $this->getPostfields();

		if ( ! $fields)
			return false;

		if ($this->postIsMedia()) {

			$response = $this->twitterApi
				->buildOauth('https://api.twitter.com/1.1/statuses/update_with_media.json', 'POST')
				->setPostfields($fields)
				->performRequest();

		} else {

			$response = $this->twitterApi
				->buildOauth('https://api.twitter.com/1.1/statuses/update.json', 'POST')
				->setPostfields($fields)
				->performRequest();
		}

		var_dump($response); exit;
	}
}