<?php

namespace App\Manager\Find;

use App\Entity\Core\User;
use App\Entity\Find\Find;
use App\Manager\AbstractAuthoredPublicationManager;

class FindManager extends AbstractAuthoredPublicationManager {

	public function publish(Find $find, $flush = true) {

		$find->getUser()->getMeta()->incrementPrivateFindCount(-1);
		$find->getUser()->getMeta()->incrementPublicFindCount();

		parent::publishPublication($find, $flush);
	}

	public function unpublish(Find $find, $flush = true) {

		$find->getUser()->getMeta()->incrementPrivateFindCount(1);
		$find->getUser()->getMeta()->incrementPublicFindCount(-1);

		parent::unpublishPublication($find, $flush);
	}

	public function delete(Find $find, $withWitness = true, $flush = true) {

		// Decrement user find count
		if ($find->getIsDraft()) {
			$find->getUser()->getMeta()->incrementPrivateFindCount(-1);
		} else {
			$find->getUser()->getMeta()->incrementPublicFindCount(-1);
		}

		parent::deletePublication($find, $withWitness, $flush);
	}

	//////

	public function changeOwner(Find $find, User $user, $flush = true) {
		parent::changeOwnerPublication($find, $user, $flush);
	}

	protected function updateUserCounterAfterChangeOwner(User $user, $by, $isPrivate) {
		if ($isPrivate) {
			$user->getMeta()->incrementPrivateFindCount($by);
		} else {
			$user->getMeta()->incrementPublicFindCount($by);
		}
	}

}