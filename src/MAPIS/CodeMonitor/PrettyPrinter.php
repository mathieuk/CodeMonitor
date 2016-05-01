<?php

namespace MAPIS\CodeMonitor;

use PhpParser\PrettyPrinter\Standard;

class PrettyPrinter extends Standard
{
	// Don't show comments
	protected function pComments(array $comments)
	{
		return '';
	}
}