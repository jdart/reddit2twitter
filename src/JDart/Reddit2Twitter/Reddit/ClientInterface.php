<?php

namespace JDart\Reddit2Twitter\Reddit;

interface ClientInterface 
{
	public function configure($config=array());
	public function getLinksFromSubreddit($subreddit='all');
}