<?php

use JDart\Reddit2Twitter\Task\TweetItem;

use Reddit\Thing\Link;

class TweetItemTest extends PHPUnit_Framework_TestCase 
{
	protected $redditResponse;
	protected $redditClient;
	protected $twitterClient;
	protected $twitterRules;
	protected $tweeter;

	protected function getUnserializedFixture($filename)
	{
		return unserialize(file_get_contents(__DIR__ . '/../Fixtures/'.$filename.'.serialized'));
	}

	protected function setUp()
	{
		$this->twitterClient = $this->getMock('\JDart\Reddit2Twitter\Twitter\ClientInterface');
		$this->twitterClient
			->expects($this->any())
			->method('getResponse')
			->will($this->returnValue(200));

		$this->twitterRules = $this->getMock('\JDart\Reddit2Twitter\Twitter\Rules', array(), array(
			'', '',
			$this->twitterClient
		));
		$this->twitterRules
			->expects($this->any())
			->method('getShortUrlLength')
			->will($this->returnValue(22));
		$this->twitterRules
			->expects($this->any())
			->method('getCharactersReservedPerMedia')
			->will($this->returnValue(23));
		$this->twitterRules
			->expects($this->any())
			->method('getMaxLength')
			->will($this->returnValue(140));

		$this->tweeter = new TweetItem($this->twitterClient, $this->twitterRules);
	}

	public function testMediaTweet()
	{
		$link = $this->getUnserializedFixture('media');

		$this->tweeter->setLink($link);

		$this->assertEquals(true, $this->tweeter->linkIsMedia());

		$title = $this->tweeter->getLinkTitle();

		$this->assertSame(false, strpos($title, 'http://'));
	}

	public function testNonMediaTweet()
	{
		$link = $this->getUnserializedFixture('non_media');

		$this->tweeter->setLink($link);

		$this->assertEquals(false, $this->tweeter->linkIsMedia());

		$title = $this->tweeter->getLinkTitle();

		$this->assertGreaterThan(0, strpos($title, 'http://'));
	}
}