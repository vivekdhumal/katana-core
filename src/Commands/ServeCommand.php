<?php

namespace Katana\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServeCommand extends Command
{
	/**
     * Configure the command.
     *
     * @return void
     */
	protected function configure()
	{
		$this->setName('serve')
            ->setDescription('Serve local site with php built-in server.')
            ->addOption(
            	'port',
            	'p',
            	InputOption::VALUE_REQUIRED,
            	'What port should we use?',
                8000
        	);
	}

	/**
     * Execute the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$port = $input->getOption('port');

		$output->writeln("<info>Started listening on http://localhost:{$port}</info>");
		passthru("php -S localhost:{$port} -t public");
	}
}
