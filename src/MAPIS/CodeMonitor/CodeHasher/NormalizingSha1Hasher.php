<?php

namespace MAPIS\CodeMonitor\CodeHasher;

use MAPIS\CodeMonitor\ICodeHasher;
use PhpParser\Node\Stmt\Class_;

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

	protected function hash($string)
	{
		return sha1($string);
	}

	public function hashFromClass(Class_ $class)
	{
		$code = $this->normalizer->pStmt_Class($class);
		return $this->hash($code);
	}

	public function hashFromMethod (\PhpParser\Node\Stmt\ClassMethod $method)
	{
		$code = $this->normalizer->pStmt_ClassMethod($method);
		return $this->hash($code);
	}

	public function hashFromFunction (\PhpParser\Node\Stmt\Function_ $function)
	{
		$code = $this->normalizer->pStmt_Function($function);
		return $this->hash($code);
	}

	public function codeFromClass(Class_ $class)
	{
		return $this->normalizer->pStmt_Class($class);
	}

	public function codeFromMethod (\PhpParser\Node\Stmt\ClassMethod $method)
	{
		return $this->normalizer->pStmt_ClassMethod($method);
	}

	public function codeFromFunction (\PhpParser\Node\Stmt\Function_ $function)
	{
		return $this->normalizer->pStmt_Function($function);
	}

}