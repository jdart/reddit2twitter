<?php 

namespace JDart\Reddit2Twitter\Task;

use JDart\Reddit2Twitter\Twitter\Rules;
use JDart\Reddit2Twitter\Twitter\ClientInterface;
use JDart\Reddit2Twitter\Exception\TweetRejectedException;
use JDart\Reddit2Twitter\Exception\InvalidMediaException;
use JDart\Reddit2Twitter\Exception\LimitExceededException;

class TweetItem
{
	protected $link;
	protected $twitterClient;
	protected $twitterRules;
	protected $files;

	public function __construct(ClientInterface $twitterClient, Rules $twitterRules)
	{
		$this->twitterClient = $twitterClient;
		$this->twitterRules = $twitterRules;
	}

	public function setLink($link)
	{
		$this->link = $link;
		$this->files = array();
	}

	public function getLinkFields()
	{
		$fields = array(
			'status' => $this->getLinkTitle(),
		);

		if ($this->linkIsMedia())
			$fields['media[]'] = $this->getMediaField();

		return $fields;
	}

	public function getMimeTypeIfValidMedia($path)
	{
		static $finfo;
		
		if ( ! isset($finfo))
			$finfo = finfo_open(FILEINFO_MIME_TYPE);

		$mime_type = finfo_file($finfo, $path);

		if ( ! in_array($mime_type, array('image/jpeg', 'image/png'), true))
			return false;

		return $mime_type;
	}

	public function resizeMedia($file)
	{
		if ($this->isMediaValidSize($file))
			return $file;

		$new_file = $file . '_resized';
		$size = filesize($file);
		$max = $this->twitterRules->getMaxPhotoSize();
		$ratio = ($max/$size) * 0.8;

		$resizer = new \Imagick($file);

		$newWidth = floor($resizer->getImageWidth() * $ratio);
		$newHeight = floor($resizer->getImageHeight() * $ratio);

		$resizer->resizeImage($newWidth, $newHeight, \Imagick::FILTER_LANCZOS, 1);
		$resizer->writeImage($new_file);
		$resizer->clear();
		$resizer->destroy(); 

		$this->files[] = $new_file;

		return $new_file;
	}

	public function getMediaField()
	{
		$url = $this->getMediaUrl();

		if ( ! ($tmp_file = $this->downloadFile($url)))
			throw new InvalidMediaException;

		if ( ! ($mime_type = $this->getMimeTypeIfValidMedia($tmp_file)))
			throw new InvalidMediaException;
		
		$tmp_file = $this->resizeMedia($tmp_file);

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

		$this->files[] = $tmp_file;

		return $tmp_file;
	}

	public function linkIsMedia()
	{
		return (bool)$this->getMediaUrl();
	}

	public function getMediaUrl()
	{
		$parts = parse_url($this->getLinkUrl());

		if (empty($parts['scheme']) || empty($parts['host']) || empty($parts['path']))
			return false;

		$normalized_url = sprintf('%s://%s%s', $parts['scheme'], $parts['host'], $parts['path']);

		if (preg_match('/\.(jpg|jpeg|png)$/', $normalized_url))
			return $normalized_url;

		return false;
	}

	public function isMediaValidSize($path)
	{
		return filesize($path) <= $this->twitterRules->getMaxPhotoSize();
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

	public function cleanUp()
	{
		foreach ($this->files as $file)
			if (is_file($file))
				unlink($file);		
	}

	public function checkForLimitExceeded()
	{
		if ( ! $this->linkIsMedia())
			return;

		$limit_remaining = (int)$this->twitterClient->getResponseHeader('x-mediaratelimit-remaining');
		$limit_reset = (int)$this->twitterClient->getResponseHeader('x-mediaratelimit-reset');

		if ($limit_remaining)
			return;

		$e = new LimitExceededException;
		$e->setNextWindow($limit_reset);
		throw $e;
	}

	public function tweet()
	{
		$this->twitterClient->postRequest(
			$this->getUpdateUrl(),
			$this->getLinkFields()
		);

		if ($this->twitterClient->getResponse() !== 200)
			throw new TweetRejectedException($this->twitterClient->getResponse());

		return true;
	}
}