<?php 

namespace JDart\Reddit2Twitter\Twitter;

class CreateTweetFromRedditPost
{
	protected $post;
	protected $twitterOauth;
	protected $twitterRules;

	public function __construct(\tmhOAuth $twitterOauth, GetTwitterRules $twitterRules)
	{
		$this->twitterOauth = $twitterOauth;
		$this->twitterRules = $twitterRules;
	}

	public function setPost($post)
	{
		$this->post = $post;
	}

	public function getPostfields()
	{
		$fields = array(
			'status' => $this->getPostTitle(),
		);

		if ($this->postIsMedia()) {

			$file_info = $this->downloadImage($this->getPostUrl());

			if ( ! $file_info)
				return false;

			$fields['media[]'] = sprintf('@%s;type=%s;filename=%s',
				$file_info['tmp_file'],
				$file_info['mime_type'],
				$file_info['filename']
			);
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
			return array(
				'tmp_file' => $tmp_file,
				'mime_type' => $type,
				'filename' => basename($url)
			);
		
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

	public function getUpdateUrl()
	{
		return $this->postIsMedia() 
			? 'https://api.twitter.com/1.1/statuses/update_with_media.json'
			: 'https://api.twitter.com/1.1/statuses/update.json';
	}

	public function execute()
	{
		$fields = $this->getPostfields();

		if ( ! $fields)
			return false;

		$response = $this->twitterOauth
			->request(
				'POST',
				$this->twitterOauth->url($this->getUpdateUrl()),
				$fields
			);

		var_dump($this->twitterOauth->response['response']); exit;
	}
}