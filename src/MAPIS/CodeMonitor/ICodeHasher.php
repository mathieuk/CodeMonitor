<?php

namespace MAPIS\CodeMonitor;

interface ICodeHasher
{
	public function toHash(\PhpParser\Node $node);
	public function toCode(\PhpParser\Node $node);
}