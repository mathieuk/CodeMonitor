<?php

namespace MAPIS\CodeMonitor;

interface INodeTransformer
{
	public function toHash(\PhpParser\Node $node);
	public function toCode(\PhpParser\Node $node);
}