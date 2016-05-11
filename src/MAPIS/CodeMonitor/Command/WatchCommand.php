<?php

namespace MAPIS\CodeMonitor\Command;

use MAPIS\CodeMonitor;
use MAPIS\CodeMonitor\Entity\StatementSignature;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Helper\QuestionHelper;

class WatchCommand extends AbstractCommand
{
	protected $db;
	protected $path;

	/**
	 * @var \Symfony\Component\Console\Helper\QuestionHelper
	 */
	protected $questionHelper;

	public function __construct()
	{
		$this->path = (realpath(getcwd()) . '/.codemon');

		parent::__construct();
	}

	public function configure()
	{
		parent::configure();

		return $this
			->setName('watch')
			->setDescription('Specify a Class, Method or Function to watch for changes')
			->addArgument(
				'identifier',
				InputArgument::REQUIRED,
				'What statement/identifier do you want to monitor?'
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->questionHelper = $this->getHelper('question');

		$identifier = $input->getArgument('identifier');
		$config     = $this->getConfiguration($input);
		$codeMon    = $this->getCodeMonitor($config);
		$repo       = $this->getRepository($config);


		$results = $codeMon->searchFilesForWatchedFunctions(
			$this->getWatchedFilesIterator($config),
			[$identifier]
		);

		if (!count($results))
		{
			throw new \RuntimeException("Could not find $identifier anywhere");
		}

		/**
		 * @var $result StatementSignature
		 */
		foreach ($results as $result)
		{
			$existingWatch = $repo->findOneByFqmn($result->getFqmn());
			
			if (!is_null($existingWatch))
			{
				$question = new ConfirmationQuestion("Identifier $identifier (as {$result->getFqmn()} in {$result->getFile()}) already being watched. Keep watching? [Y/n]:");
				
				if (!$this->questionHelper->ask($input, $output, $question))
				{
					$repo->delete($existingWatch);
					$output->writeln("[REMOVED] {$result->getFqmn()}");
					continue;
				}
			}
			else 
			{
				$question = new ConfirmationQuestion("Found $identifier as {$result->getFqmn()} in {$result->getFile()}. Start watching? [Y/n]:");
				if ($this->questionHelper->ask($input, $output, $question))
				{
					$repo->store($result);
					$output->writeln("[ADDED] {$result->getFqmn()}");
				}
			}
		}
	}
}