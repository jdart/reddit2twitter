<?php 

namespace JDart\Reddit2Twitter\Task;

use JDart\Reddit2Twitter\Twitter\Rules;
use JDart\Reddit2Twitter\Twitter\ClientInterface;
use JDart\Reddit2Twitter\Exception\TweetFailedException;

class TweetItem
{
	protected $link;
	protected $twitterClient;
	protected $twitterRules;

	public function __construct(ClientInterface $twitterClient, Rules $twitterRules)
	{
		$this->twitterClient = $twitterClient;
		$this->twitterRules = $twitterRules;
	}

	public function setLink($link)
	{
		$this->link = $link;
	}

	public function getLinkFields()
	{
		$fields = array(
			'status' => $this->getLinkTitle(),
		);

		if ($this->linkIsMedia()) {

			if ( ! ($mediaField = $this->getMediaField()))
				return false;

			$fields['media[]'] = $mediaField;
		}

		return $fields;
	}

	public function getMediaField()
	{
		static $finfo;

		if ( ! isset($finfo))
			$finfo = finfo_open(FILEINFO_MIME_TYPE);

		$url = $this->getLinkUrl();

		if ( ! ($tmp_file = $this->downloadFile($url)))
			return false;

		$mime_type = finfo_file($finfo, $tmp_file);

		if ( ! in_array($mime_type, array('image/jpeg', 'image/png'), true))
			return false;

		return sprintf('@%s;type=%s;filename=%s',
			$tmp_file,
			$mime_type,
			basename($url)
		);
	}

	public function downloadFile($url)
	{
		static $ch;

		if ( ! isset($ch))
			$ch = curl_init();

		$tmp_file = '/tmp/' . md5($url);

		if (is_file($tmp_file))
			return $tmp_file;

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

		return $tmp_file;
	}

	public function linkIsMedia()
	{
		$path = parse_url($this->getLinkUrl(), PHP_URL_PATH);
		
		return preg_match('/\.(jpg|jpeg|png)$/', $path);
	}

	public function getLinkUrl()
	{
		if (empty($this->link->url))
			return false;

		return $this->link->url;
	}

	public function getLinkTitle()
	{
		$length = $this->twitterRules->getMaxLength();
		$append = '';

		if ($url = $this->getLinkUrl()) {

			if ($this->linkIsMedia()) {
				$length = $length - $this->twitterRules->getCharactersReservedPerMedia() - 1;
			} else {
				$length = $length - $this->twitterRules->getShortUrlLength() - 1;
				$append = ' ' . $url;
			}
		}

		return substr($this->link->title, 0, $length) . $append;
	}

	public function getUpdateUrl()
	{
		return $this->linkIsMedia() 
			? 'https://api.twitter.com/1.1/statuses/update_with_media.json'
			: 'https://api.twitter.com/1.1/statuses/update.json';
	}

	public function getNextWindow()
	{
		if ($this->linkIsMedia()) {

			$limit_remaining = (int)$this->twitterClient->getResponseHeader('x-mediaratelimit-remaining');
			$limit_reset = (int)$this->twitterClient->getResponseHeader('x-mediaratelimit-reset');

		} else {

			$limit_remaining = (int)$this->twitterClient->getResponseHeader('x-ratelimit-remaining');
			$limit_reset = (int)$this->twitterClient->getResponseHeader('x-ratelimit-reset');
		}

		if ($limit_remaining)
			return 0;

		return $limit_reset;
	}

	public function tweet()
	{
		$fields = $this->getLinkFields();

		if ( ! $fields)
			return false;

		$this->twitterClient->postRequest(
			$this->getUpdateUrl(),
			$fields
		);

		if ($this->twitterClient->getResponse() !== 200)
			throw new TweetFailedException($this->twitterClient->getResponse());
		
		return true;
	}
}