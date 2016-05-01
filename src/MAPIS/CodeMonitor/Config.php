<?php

namespace MAPIS\CodeMonitor;

class Config
{
	public $sourcePath;
	public $dbPath;
	public $notifierClass;
	public $notifierArguments;

	public function isComplete()
	{
		return
			!empty($this->sourcePath) &&
			!empty($this->dbPath) &&
			!empty($this->notifierClass);

	}
}