<?php

namespace MAPIS;

use MAPIS\CodeMonitor\CodeHasher\NormalizingSha1Hasher;
use MAPIS\CodeMonitor\Finding;
use MAPIS\CodeMonitor\FunctionCollectingVisitor;
use MAPIS\CodeMonitor\PrettyPrinter;
use MAPIS\CodeMonitor\Repository\SignatureRepository;
use MAPIS\CodeMonitor\Entity\StatementSignature;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\NodeVisitor\NameResolver;
use SebastianBergmann\Diff\Differ;

class CodeMonitor
{
	public function __construct($files, SignatureRepository $repo)
	{
		$this->fileIterator = $files;
		$this->repo         = $repo;
	}

	public function getDiffForCode($knownCode, $currentCode)
	{
		$differ = new Differ;
		return $differ->diff($knownCode, $currentCode);
	}
	/**
	 * Slow scan of going through all files and finding a function or method.
	 */
	protected function resolveUnseenFunctions()
	{
		// Find all the watches for which we don't have a file yet

		$unresolvedWatchedFunctions = array_filter(
			$this->repo->getWatchedMethods(),
			function(StatementSignature $element) { return empty($element->getFile()); }
		);

		$unresolvedWatchedFQMNs = array_map(
			function (StatementSignature $element) { return $element->getFQMN(); },
			$unresolvedWatchedFunctions
		);

		if (!count($unresolvedWatchedFunctions))
			return;

		$foundFunctions = $this->searchFilesForWatchedFunctions(
			$this->fileIterator,
			$unresolvedWatchedFQMNs
		);

		// Update the database with files and hashes

		/**
		 * @var $codeSig StatementSignature
		 */
		foreach ($foundFunctions as $fqmn => $codeSig)
		{
			$this->repo->store($codeSig);
		}

	}

	function performCheck()
	{
		$files = $watches = [];
		$watchedMethods   = [];

		// Find files and hashes for newly added watches.
		$this->resolveUnseenFunctions();

		// Go through all defined watches and see if something changed.
		$rawWatchedMethods = $this->repo->getWatchedMethods();

		/**
		 * @var $methodSig StatementSignature
		 */
		foreach ($rawWatchedMethods as $methodSig)
		{

			if (!$methodSig->getFile() || !$methodSig->getHash())
			{
				// $this->logger->info('Skipping watched ' . $methodSig->getName() . ': not resolved');
				continue;
			}

			$watchedMethods[$methodSig->getFqmn()] = $methodSig;

			$files[]   = $methodSig->getFile();
			$watches[] = $methodSig->getFQMN();
		}

		/**
		 * @var $results StatementSignature[]
		 */
		$results  = $this->searchFilesForWatchedFunctions($files, $watches);
		$findings = [];

		foreach ($watchedMethods as $fqmn => $methodSig)
		{
			if (!isset($results[$fqmn]))
			{
				// $this->logger->debug("Expected $fqmn to be in the sourcePath but couldnt find it");
				continue;
			}

			if ($results[$fqmn]->getHash() != $methodSig->getHash())
			{
				$findings[] = new Finding(
					$methodSig,
					$this->getDiffForCode($methodSig->getCode(), $results[$fqmn]->getCode())
				);

				$this->repo->store($results[$fqmn]);
			}
		}

		return $findings;
	}
		
	public function searchFilesForWatchedFunctions($files, $watchedIdentifiers)
	{
		$parserFactory = new ParserFactory();
		$foundMethods  = [];

		foreach ($files as $file)
		{
			$visitor = new FunctionCollectingVisitor(
				new NormalizingSha1Hasher(new PrettyPrinter()),
				$watchedIdentifiers,
				$file
			);

			$nodeTraverser = new NodeTraverser();
			$nodeTraverser->addVisitor(new NameResolver());
			$nodeTraverser->addVisitor($visitor);

			$parser = $parserFactory->create(ParserFactory::PREFER_PHP7);
			$stmts  = $parser->parse(file_get_contents($file));
			$nodeTraverser->traverse($stmts);

			$foundMethods = array_merge($foundMethods, $visitor->getFoundMethods());
		}

		return $foundMethods;
	}

}