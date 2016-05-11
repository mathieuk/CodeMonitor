<?php

namespace MAPIS\CodeMonitor\CodeHasher;

use MAPIS\CodeMonitor\ICodeHasher;
use PhpParser\Node;

class NormalizingSha1Hasher
	implements ICodeHasher
{
	/**
	 * @var \MAPIS\CodeMonitor\PrettyPrinter
	 */
	protected $normalizer;

	public function __construct(\MAPIS\CodeMonitor\PrettyPrinter $normalizer)
	{
		$this->normalizer = $normalizer;
	}

	public function toHash(Node $node)
	{
		$code = $this->toCode($node);
		return sha1($code);
	}
	
	public function toCode(Node $node)
	{
		switch (get_class($node))
		{
			case Node\Stmt\Class_::class:
				$code = $this->normalizer->pStmt_Class($node);
				break;
			
			case Node\Stmt\Function_::class:
				$code = $this->normalizer->pStmt_Function($node);
				break;
				
			case Node\Stmt\ClassMethod::class:
				$code = $this->normalizer->pStmt_ClassMethod($node);
				break;
		
			default:
				throw new \InvalidArgumentException("Don't know how to obtain code for instance of: " . get_class($class));
		}
		
		return $code;
	}
}