<?php 

namespace JDart\Reddit2Twitter\Task;

class CreateTweetFromRedditLink
{
	protected $link;
	protected $twitterOauth;
	protected $twitterRules;

	public function __construct(\tmhOAuth $twitterOauth, \JDart\Reddit2Twitter\Twitter\GetRules $twitterRules)
	{
		$this->twitterOauth = $twitterOauth;
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
		if ($url = $this->getLinkUrl())
			return preg_match('/\.(jpg|jpeg|png)$/', $url);
		
		return false;
	}

	public function getLinkUrl()
	{
		if (empty($this->link->url))
			return false;

		return $this->link->url;
	}

	public function getLinkTitle()
	{
		$length = 140;
		$append = '';

		if ($url = $this->getLinkUrl()) {

			if ($this->linkIsMedia()) {
				$length = 140 - $this->twitterRules->getCharactersReservedPerMedia() - 1;
			} else {
				$length = 140 - $this->twitterRules->getShortUrlLength() - 1;
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

	public function tweet()
	{
		$fields = $this->getLinkFields();

		if ( ! $fields)
			return false;

		$response = $this->twitterOauth
			->request(
				'POST',
				$this->twitterOauth->url($this->getUpdateUrl()),
				$fields,
				true,
				true
			);
	}
}