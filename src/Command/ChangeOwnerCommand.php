<?php

namespace App\Command;

use App\Entity\Core\User;
use App\Utils\TypableUtils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ChangeOwnerCommand extends AbstractContainerAwareCommand {

	protected function configure() {
		$this
			->setName('ladb:change:owner')
			->addOption('target-username', null, InputOption::VALUE_REQUIRED, 'Target username')
			->addOption('entity-type', null, InputOption::VALUE_REQUIRED, 'Entity type')
			->addOption('entity-id', null, InputOption::VALUE_REQUIRED, 'Entity ID')
			->addOption('force', null, InputOption::VALUE_NONE, 'Force removing')
			->setDescription('Change owner')
			->setHelp(<<<EOT
The <info>ladb:change:owner</info> command change owner
EOT
		);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {

		$forced = $input->getOption('force');
		$targetUsername = $input->getOption('target-username');
		$entityType = $input->getOption('entity-type');
		$entityId = $input->getOption('entity-id');

		$om = $this->getDoctrine()->getManager();

		// Retrieve target user ////

		$userRepository = $om->getRemository(User::class);
		$targetUser = $userRepository->findOneByUsername($targetUsername);

		if (is_null($targetUser)) {
			$output->writeln('<error>Unknow unsername='.$targetUsername.'</error>', 0);
			return Command::FAILURE;
		}

		// Retrieve creation ////

		$typableUtils = $this->get(TypableUtils::class);
		$typable = $typableUtils->findTypable($entityType, $entityId);

		$output->write('<info>Changing Owner of typable="'.$typable->getTitle().'" to targetUser='.$targetUser->getDisplayName().'...</info>');

		$manager = $typableUtils->getManagerByType($entityType);
		if (!is_null($manager)) {
			$manager->changeOwner($typable, $targetUser, false);
		}

		if ($forced) {
			$om->flush();
			$output->writeln('<fg=cyan>[Done]</fg=cyan>');
		} else {
			$output->writeln('<fg=cyan>[Fake]</fg=cyan>');
		}

        return Command::SUCCESS;

	}

}