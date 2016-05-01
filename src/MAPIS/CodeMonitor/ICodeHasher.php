<?php

namespace MAPIS\CodeMonitor;

interface ICodeHasher
{
	public function hashFromClass(\PhpParser\Node\Stmt\Class_ $class);
	public function hashFromMethod(\PhpParser\Node\Stmt\ClassMethod $method);
	public function hashFromFunction(\PhpParser\Node\Stmt\Function_ $function);
	
	public function codeFromClass(\PhpParser\Node\Stmt\Class_ $class);
	public function codeFromMethod(\PhpParser\Node\Stmt\ClassMethod $method);
	public function codeFromFunction(\PhpParser\Node\Stmt\Function_ $function);
}