<?php

namespace JDart\Reddit2Twitter\Twitter;

use \JDart\Reddit2Twitter\Twitter\ClientInterface;
use \tmhOAuth;

class ClientTmhOauth implements ClientInterface
{
	protected $tmhOAuth;
	protected $response;
	protected $responseObject;

	public function __construct()
	{
		$this->tmhOAuth = new tmhOAuth;
	}

	public function configure($config)
	{
		$this->tmhOAuth->reconfigure($config);
	}

	public function postRequest($url, $params=array()) 
	{
		// die('posted');

		$this->response = $this->tmhOAuth->request(
			'POST',
			$this->tmhOAuth->url($url),
			$params,
			true,
			true
		);

		$this->responseObject = $this->tmhOAuth->response;
	}

	public function getRequest($url, $params=array())
	{
		// die('getted');

		$this->response = $this->tmhOAuth->request(
			'GET',
			$this->tmhOAuth->url($url),
			$params
		);

		$this->responseObject = $this->tmhOAuth->response;
	}

	public function getResponse()
	{
		return $this->response;
	}

	public function getResponseBody()
	{
		if (isset($this->responseObject['response']))
			return $this->responseObject['response'];

		return null;
	}

	public function getResponseHeader($key)
	{
		if (isset($this->responseObject['headers'][$key]))
			return $this->responseObject['headers'][$key];

		return null;
	}
}