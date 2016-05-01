<?php

namespace MAPIS\CodeMonitor;

use MAPIS\CodeMonitor\Entity\StatementSignature;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\PrettyPrinter\Standard;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Function_;



class FunctionCollectingVisitor extends NodeVisitorAbstract
{
	protected $file;

	protected $methods;
	protected $foundMethods = [];
	/**
	 * @var Standard
	 */
	protected $prettyPrinter;

	public function __construct(ICodeHasher $hasher, $watchedFunctions, $file)
	{
		$this->codeHasher    = $hasher;
		$this->methods       = $watchedFunctions;
		$this->file          = $file;
	}

	public function leaveNode(Node $node) {

		switch(get_class($node))
		{
			case Node\Stmt\Class_::class:
				/**
				 * @var $node Class_
				 */
				$this->parseClass($node);
				break;

			case Node\Stmt\Function_::class:
				/**
				 * @var $node Function_
				 */
				$this->parseFunction($node);
				break;
		}
	}

	protected function parsefunction(Function_ $node)
	{
		if (in_array($node->name, $this->methods))
		{
			$codeSig = new StatementSignature();
			$codeSig->setFqmn($node->name);
			$codeSig->setFile($this->file);
			$codeSig->setHash($this->codeHasher->hashFromFunction($node));
			$codeSig->setCode($this->codeHasher->codeFromFunction($code));

			$this->foundMethods[$node->name] = $codeSig;
		}
	}

	protected function parseClass(Node\Stmt\Class_ $node)
	{
		// Did we want to watch this entire class?
		if (in_array($node->name, $this->methods))
		{
			$codeSig = new StatementSignature();
			$codeSig->setFqmn($node->name);
			$codeSig->setFile($this->file);
			$codeSig->setHash($this->codeHasher->hashFromClass($node));
			$codeSig->setCode($this->codeHasher->codeFromClass($node));

			$this->foundMethods[$node->name] = $codeSig;

		}
		// Did we (also) want to watch a specific method?
		foreach ($node->stmts as $stmt)
		{
			if ($stmt instanceof Node\Stmt\ClassMethod)
			{
				$methodName = $node->name . '::' . $stmt->name;

				if (in_array($methodName, $this->methods))
				{
					$codeSig = new StatementSignature();
					$codeSig->setFqmn($methodName);
					$codeSig->setFile($this->file);
					$codeSig->setHash($this->codeHasher->hashFromMethod($stmt));
					$codeSig->setCode($this->codeHasher->codeFromMethod($stmt));

					$this->foundMethods[$methodName] = $codeSig;
				}
			}
		}
	}

	public function getFoundMethods()
	{
		return $this->foundMethods;
	}
}