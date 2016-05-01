<?php

namespace MAPIS\CodeMonitor\Notifier;

use MAPIS\CodeMonitor\INotifier;
use MAPIS\CodeMonitor\Finding;

class StdoutNotifier
	implements INotifier
{
	public function notify($findings)
	{
		/**
		 * @var $finding Finding
		 */
		foreach ($findings as $finding)
		{
			$signature = $finding->getStatementSig();
			printf("[CHANGED] %s: %s\n", $signature->getFile(), $signature->getFqmn());
			echo $finding->getDiff();
			echo "\n\n";
		}
	}
}