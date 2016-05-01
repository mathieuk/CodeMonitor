<?php

namespace MAPIS\CodeMonitor\Command;

use MAPIS\CodeMonitor;
use MAPIS\CodeMonitor\Config;
use MAPIS\CodeMonitor\Repository\SignatureRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

abstract class AbstractCommand extends Command
{
	const CONFIG_SOURCEPATH_KEY    = 'sourcePath';
	const CONFIG_DBPATH_KEY        = 'dbPath';
	const CONFIG_NOTIFIERCLASS_KEY = 'notifierClass';
	const CONFIG_NOTIFIERARGS_KEY  = 'notifierArgs';

	public function configure()
	{
		return $this->addOption('config', 'c', InputArgument::OPTIONAL, 'Path to configuration file');
	}

	/**
	 * @param InputInterface $input
	 * @return Config
	 */
	public function getConfiguration(InputInterface $input)
	{
		$configPath = $input->getOption('config');

		if (empty($configPath))
		{
			$path = realpath(getcwd()) . '/.codemon/config.json';

			if (!is_readable($path))
				throw new \InvalidArgumentException("Cannot read configuration from $path. Provide path to config with [-c|--config]");
		}

		$path = $configPath;

		if (!is_readable($path))
			throw new \InvalidArgumentException("Cannot read configuration from $path");

		return $this->parseConfigurationFile($path);
	}

	protected function getWatchedFilesIterator(Config $config)
	{
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($config->sourcePath),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		return new \CallbackFilterIterator(
			$iterator,
			function($current, $key, $iterator)
			{
				return preg_match('~.php$~i', (string) $current) > 0;
			}
		);
	}
	protected function getRepository(Config $config)
	{
		$db      = new \PDO($q = 'sqlite:' . $config->dbPath);
		$repo    = new SignatureRepository($db);

		return $repo;
	}
	protected function getCodeMonitor(Config $config)
	{

		return new CodeMonitor(
			$this->getWatchedFilesIterator($config),
			$this->getRepository($config)
		);
	}

	protected function parseConfigurationFile($file)
	{
		$json_data = file_get_contents($file);
		$data      = json_decode($json_data);
		$config    = new Config();

		foreach ($data as $key => $value)
		{
			switch ($key)
			{
				case self::CONFIG_SOURCEPATH_KEY:
					$config->sourcePath = (string) $value;
					break;

				case self::CONFIG_DBPATH_KEY:
					$config->dbPath = (string) $value;
					break;

				case self::CONFIG_NOTIFIERCLASS_KEY:
					$config->notifierClass = (string) $value;
					break;

				case self::CONFIG_NOTIFIERARGS_KEY:
					$config->notifierArguments = $value;
					break;
			}
		}

		if (!$config->isComplete())
		{
			throw new \RuntimeException("Configuration incomplete. dbPath, notifierClass are required");
		}

		return $config;
	}
}