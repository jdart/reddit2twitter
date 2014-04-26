<?php

namespace JDart\Reddit2Twitter\Twitter;

interface ClientInterface 
{
	public function configure($config);
	public function postRequest($url, $params=array());
	public function getRequest($url, $params=array());
	public function getResponseBody();
	public function getResponse();
	public function getResponseHeader($key);
}