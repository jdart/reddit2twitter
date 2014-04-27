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
	protected $fixturesPath;

	protected function getUnserializedFixture($filename)
	{
		return unserialize(file_get_contents($this->fixturesPath . $filename . '.serialized'));
	}

	protected function setUp()
	{
		$this->fixturesPath = __DIR__ . '/../Fixtures/';

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
		$this->twitterRules
			->expects($this->any())
			->method('getMaxPhotoSize')
			->will($this->returnValue(3145728));

		$this->tweeter = new TweetItem($this->twitterClient, $this->twitterRules);
	}

	public function testMediaTweet()
	{
		$link = $this->getUnserializedFixture('media');

		$this->tweeter->setLink($link);

		$this->assertEquals(true, $this->tweeter->linkIsMedia());

		$title = $this->tweeter->getLinkTitle();

		$this->assertSame(false, strpos($title, 'http://'));

		$this->assertNotEquals(false, $this->tweeter->getMediaField());

		$link->url = 'http://test.com/foo.jpg?something=ugh';

		$this->assertSame($this->tweeter->getMediaUrl(), 'http://test.com/foo.jpg');
	}

	public function testNonMediaTweet()
	{
		$link = $this->getUnserializedFixture('non_media');

		$this->tweeter->setLink($link);

		$this->assertEquals(false, $this->tweeter->linkIsMedia());

		$title = $this->tweeter->getLinkTitle();

		$this->assertGreaterThan(0, strpos($title, 'http://'));
	}

	public function testInvalidMediaException()
	{
        $this->setExpectedException('\JDart\Reddit2Twitter\Exception\InvalidMediaException');

        $link = $this->getUnserializedFixture('non_media');

		$this->tweeter->setLink($link);

		$this->tweeter->getMediaField();
	}

	public function testMediaManipulation()
	{
		$this->assertSame('image/jpeg', $this->tweeter->getMimeTypeIfValidMedia($this->fixturesPath.'valid.jpg'));

		$this->assertSame(false, $this->tweeter->getMimeTypeIfValidMedia($this->fixturesPath.'animated.gif'));

		$this->assertSame(false, $this->tweeter->getMimeTypeIfValidMedia($this->fixturesPath.'invalid.jpg'));

		$this->assertSame(false, $this->tweeter->isMediaValidSize($this->fixturesPath.'too_big.jpg'));

		$this->assertSame(true, $this->tweeter->isMediaValidSize($this->fixturesPath.'valid.jpg'));

		$big_image = $this->fixturesPath.'too_big.jpg';

		$resized = $this->tweeter->resizeMedia($big_image);

		$this->assertNotEquals($big_image, $resized);

		$this->assertTrue($this->tweeter->isMediaValidSize($resized));

		unlink($resized);
	}
}