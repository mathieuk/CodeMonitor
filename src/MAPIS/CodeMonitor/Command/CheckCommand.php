<?php

namespace MAPIS\CodeMonitor\Command;

use MAPIS\CodeMonitor\INotifier;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CheckCommand extends AbstractCommand
{
	public function configure()
	{
		parent::configure();

		return $this
			->setName('check')
			->setDescription('Check the codebase for any changes in the watched statements');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$config   = $this->getConfiguration($input);
		$codeMon  = $this->getCodeMonitor($config);

		$class = $config->notifierClass;
		/**
		 * @var $notifier INotifier */
		$notifier = new $class($config->notifierArguments);
		$findings = $codeMon->performCheck();

		$notifier->notify($findings);
	}
}