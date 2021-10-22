<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class BackupDumpdbCommand extends AbstractCommand {

	protected function configure() {
		$this
			->setName('ladb:backup:dumpdb')
			->addOption('dump-dir', null, InputOption::VALUE_REQUIRED, 'Define the dump dir', 'backups')
			->addOption('compress', null, InputOption::VALUE_NONE, 'Define if dump file is compressed')
			->addOption('overwrite', null, InputOption::VALUE_NONE, 'Define previous dump file is overwrited')
			->addOption('table', null, InputOption::VALUE_REQUIRED, 'Dump this table ONLY')
			->addOption('ignore-table', null, InputOption::VALUE_REQUIRED, 'Ignore given table (write without DB name)')
			->addOption('where', null, InputOption::VALUE_REQUIRED, 'Dump only selected records. Quotes are mandatory.')
			->setDescription('Dump the database')
			->setHelp(<<<EOT
The <info>ladb:backup:db</info> command dump the database
EOT
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$dumpDir = $input->getOption('dump-dir');
		$compress = $input->getOption('compress');
		$overwrite = $input->getOption('overwrite');
		$table = $input->getOption('table');
		$ignoreTable = $input->getOption('ignore-table');
		$where = $input->getOption('where');


        $dbUri = $this->getParameter('doctrine_uri');
        // Todo set parameters from doctrine url
		$dbHost = '';
		$dbPort = '';
		$dbUser = '';
		$dbPassword = '';
		$dbName = '';
		$sqlFile = date('Ymd_His').'.'.$dbName.(!is_null($table) ? '.'.$table : '').'.sql';
		$fs = new Filesystem();

		// Compute the mysqldump command

		$mysqldumpCommand = 'mysqldump';

		if (!is_null($dbUser)) {
			$mysqldumpCommand .= ' --user='.$dbUser;
		}
		if (!is_null($dbPassword)) {
			$mysqldumpCommand .= ' --password='.$dbPassword;
		}
		if (!is_null($dbHost)) {
			$mysqldumpCommand .= ' --host='.$dbHost;
		}
		if (!is_null($dbPort)) {
			$mysqldumpCommand .= ' --port='.$dbPort;
		}
		if (!is_null($ignoreTable)) {
			$mysqldumpCommand .= ' --ignore-table='.$dbName.'.'.$ignoreTable;
		}
		if (!is_null($where)) {
			$mysqldumpCommand .= ' --where="'.$where.'"';
		}
		if (!is_null($dbName)) {
			$mysqldumpCommand .= ' '.$dbName;
		}
		if (!is_null($table)) {
			$mysqldumpCommand .= ' '.$table;
		}
		if (!empty($dumpDir)) {

			$sqlFile = $dumpDir.'/'.$sqlFile;

			// Create dumpDir if it does not exist
			if (!$fs->exists($dumpDir)) {
				$fs->mkdir($dumpDir);
			}

		}

		// Remove previous dump
		if ($fs->exists($sqlFile)) {
			if ($overwrite) {
				$fs->remove($sqlFile);
			} else {
				$output->writeln('<error>A previous dump already exists. Use --overwrite to overwrite it.</error>');
                return Command::FAILURE;
			}
		}

		// Execute mysqldump command
		$mysqldumpCommand .= ' > '.$sqlFile;
		$output->writeln('<info>Executing mysqldump command ... </info>');
		$output->writeln(' '.$mysqldumpCommand);
		if (system($mysqldumpCommand) === false) {
			$output->writeln('<error>Error executing mysqldump command.</error>');
			return Command::FAILURE;
		}

		if ($compress && $fs->exists($sqlFile)) {

			$gzFile = $sqlFile.'.gz';

			if ($fs->exists($gzFile)) {
				$fs->remove($gzFile);
			}

			// Execute gzip command
			$tarCommand = 'gzip < '.$sqlFile.' > '.$gzFile;
			if (system($tarCommand) === false) {
				$output->writeln('<error>Error executing gzip command.</error>');
                return Command::FAILURE;
			}

		}

		$output->writeln('<info>Dump complete at </info>'.$sqlFile.($compress ? '<info> and </info>'.$gzFile : ''));
        return Command::SUCCESS;
	}

}