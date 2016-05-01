<?php

namespace MAPIS\CodeMonitor;

interface INotifier
{
	/**
	 * @param $findings Finding[]
	 * @return void
	 */
	public function notify($findings);
}