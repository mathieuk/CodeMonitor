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
	protected $docblockTags = [];
	protected $foundMethods = [];
	/**
	 * @var Standard
	 */
	protected $prettyPrinter;

	public function __construct(ICodeHasher $hasher, $watchedFunctions, $file)
	{
		$this->codeHasher    = $hasher;
		
		// Filter out any doctags
		$this->methods       = array_filter(
			$watchedFunctions,
			function($element) { return $element[0] != '@'; }
		);
		
		$this->doctags = array_filter(
			$watchedFunctions, 
			function($element) { return $element[0] == '@'; }
		);
		
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

	protected function interestedIn(Node $node)
	{
		if (
			in_array($node->name, $this->methods) ||
			in_array($node->namespacedName, $this->methods))
		{
			return TRUE;
		}
		
		return $this->interestedInDocblock($node);
	}
	
	protected function interestedInDocblock(Node $node)
	{
		$comments = $node->getAttribute('comments');
		
		if ($comments)
		{
			foreach($comments as $comment)
			{
				foreach ($this->doctags as $tag)
				{
					if (stripos($comment, $tag) !== FALSE)
					{
						return TRUE;
					}
				}
			}
		}

		return FALSE;
	}
	

	protected function parseFunction(Function_ $node)
	{
		if ($this->interestedIn($node))
		{
			$codeSig = new StatementSignature();
			$codeSig->setFqmn($node->namespacedName);
			$codeSig->setFile($this->file);
			$codeSig->setHash($this->codeHasher->hashFromFunction($node));
			$codeSig->setCode($this->codeHasher->codeFromFunction($node));

			$this->foundMethods[$node->name] = $codeSig;
		}
	}

	protected function parseClass(Node\Stmt\Class_ $node)
	{
		// Did we want to watch this entire class?
		if ($this->interestedIn($node))
		{
			$codeSig = new StatementSignature();
			$codeSig->setFqmn($node->namespacedName);
			$codeSig->setFile($this->file);
			$codeSig->setHash($this->codeHasher->hashFromClass($node));
			$codeSig->setCode($this->codeHasher->codeFromClass($node));

			$this->foundMethods[(string)$node->namespacedName] = $codeSig;
		}
		// Did we (also) want to watch a specific method?
		foreach ($node->stmts as $stmt)
		{
			if ($stmt instanceof Node\Stmt\ClassMethod)
			{

				$namespacedMethodName = $node->namespacedName . '::' . $stmt->name;
				$methodName = $node->name . '::' . $stmt->name;

				if (
					in_array($methodName, $this->methods) || 
					in_array($namespacedMethodName, $this->methods) || 
					$this->interestedInDocblock($stmt))
				{
					$codeSig = new StatementSignature();
					$codeSig->setFqmn($namespacedMethodName);
					$codeSig->setFile($this->file);
					$codeSig->setHash($this->codeHasher->hashFromMethod($stmt));
					$codeSig->setCode($this->codeHasher->codeFromMethod($stmt));

					$this->foundMethods[$namespacedMethodName] = $codeSig;
				}
			}
		}
	}

	public function getFoundMethods()
	{
		return $this->foundMethods;
	}
}
