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
		
		// Collect all the doctags
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
				
				if ($this->interestedIn($node))
				{
					$this->addStatementOfInterest($node, (string) $node->namespacedName);
				}
				// Did we (also) want to watch a specific method?
				foreach ($node->stmts as $stmt)
				{
					if ($stmt instanceof Node\Stmt\ClassMethod && $this->interestedIn($stmt, $node))
					{
						$namespacedMethodName = $node->namespacedName . '::' . $stmt->name;
						$this->addStatementOfInterest($stmt, $namespacedMethodName);
					}
				}

				break;

			case Node\Stmt\Function_::class:
				/**
				 * @var $node Function_
				 */
				
				if ($this->interestedIn($node))
				{
					$this->addStatementOfInterest($node, $node->namespacedName);
				}
				
				break;
		}
	}

	protected function interestedIn(Node $node, $class = NULL)
	{
		$defaultName         = $node->name;
		$fullyQualifiedName  = $node->namespacedName ? $node->namespacedName : $node->name;

		if (!is_null($class) && !$class instanceof Node\Stmt\Class_)
			throw new InvalidArgumentException("Supplied \$class must be of type Class_");
		
		if (!is_null($class))
		{
			$defaultName        = $class->name . '::' . $defaultName;
			$fullyQualifiedName = $class->namespacedName . '::' . $fullyQualifiedName;
		} 
		
		if (in_array($defaultName, $this->methods) ||
			in_array($fullyQualifiedName, $this->methods) || 
			$this->interestedInDocblock($node))
		{
			return TRUE;
		}
		
		return FALSE;
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
						return TRUE;
				}
			}
		}

		return FALSE;
	}
	
	protected function addStatementOfInterest(Node $node, $registerName)
	{
		$codeSig = new StatementSignature();
		$codeSig->setFqmn($registerName);
		$codeSig->setFile($this->file);
		$codeSig->setHash($this->codeHasher->toHash($node));
		$codeSig->setCode($this->codeHasher->toCode($node));
	
		$this->foundMethods[$registerName] = $codeSig;
	}

	public function getFoundMethods()
	{
		return $this->foundMethods;
	}
}
