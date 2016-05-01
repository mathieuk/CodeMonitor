<?php

namespace MAPIS\CodeMonitor\Command;

use MAPIS\CodeMonitor\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class InitCommand extends Command
{
	protected $db;
	protected $path;
	protected $dbPath;
	protected $questionHelper;

	public function configure()
	{
		return $this
			->setName('init')
			->setDescription('Initialize monitoring tooling');
	}

	protected function initializeDatabase()
	{
		$db     = new \PDO($q = 'sqlite:' . $this->dbPath);
		$result = $db->query("CREATE TABLE IF NOT EXISTS statement_signature ( fqmn VARCHAR(255) UNIQUE, hash VARCHAR(64) , file VARCHAR(255), code TEXT);");

		if (!$result)
		{
			throw new \RuntimeException("Failed to initialize database" . var_export($db->errorInfo(), TRUE));
		}
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		/** @var $helper */
		$this->questionHelper = $this->getHelper('question');

		$suggestedPath = (realpath(getcwd()));
		$question      = new Question("Enter source base path [$suggestedPath]: ", $suggestedPath);
		$this->path    = $this->questionHelper->ask($input, $output, $question);

		if (!realpath($this->path))
		{
			throw new InvalidArgumentException("{$this->path} does not exist");
		}

		$this->path      = realpath($this->path);
		$suggestedDbPath = $this->path . '/.codemon/';

		$question      = new Question(
			"Enter directory to store my data in [$suggestedDbPath]: ",
			$suggestedDbPath
		);

		$this->dbPath    = $this->questionHelper->ask($input, $output, $question);

		if (!is_dir($this->dbPath) && !@mkdir($this->dbPath, 0700, true))
		{
			throw new \RuntimeException("Unable to create path $this->dbPath");
		}

		$this->dbPath .= 'sigs.db';

		if (!file_exists($this->dbPath))
			touch($this->dbPath);

		$output->writeln("Wrote database structure to $this->dbPath");
		$this->initializeDatabase();


		$config = new Config();
		$config->sourcePath        = $this->path;
		$config->dbPath            = $this->dbPath;
		$config->notifierClass     = "MAPIS\\CodeMonitor\\Notifier\\StdoutNotifier";
		$config->notifierArguments = "";

		$configPath = dirname($this->dbPath) . '/config.json';
		$result     = json_encode($config);
		file_put_contents($configPath, $result);

		$output->writeln("Wrote configuration to $configPath");
	}
}