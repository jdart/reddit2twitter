<?php

namespace JDart\Reddit2Twitter\Exception;

class LimitExceededException extends \Exception
{
	protected $nextWindow;

	public function setNextWindow($nextWindow)
	{
		$this->nextWindow = $nextWindow;
	}

	public function getNextWindow()
	{
		return $this->nextWindow;
	}
}