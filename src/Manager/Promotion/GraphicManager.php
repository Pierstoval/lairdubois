<?php

namespace App\Manager\Promotion;

use App\Entity\Core\User;
use App\Entity\Promotion\Graphic;
use App\Manager\AbstractAuthoredPublicationManager;

class GraphicManager extends AbstractAuthoredPublicationManager {

	public function publish(Graphic $graphic, $flush = true) {

		$graphic->getUser()->getMeta()->incrementPrivateGraphicCount(-1);
		$graphic->getUser()->getMeta()->incrementPublicGraphicCount();

		parent::publishPublication($graphic, $flush);
	}

	public function unpublish(Graphic $graphic, $flush = true) {

		$graphic->getUser()->getMeta()->incrementPrivateGraphicCount(1);
		$graphic->getUser()->getMeta()->incrementPublicGraphicCount(-1);

		parent::unpublishPublication($graphic, $flush);
	}

	public function delete(Graphic $graphic, $withWitness = true, $flush = true) {

		// Decrement user graphic count
		if ($graphic->getIsDraft()) {
			$graphic->getUser()->getMeta()->incrementPrivateGraphicCount(-1);
		} else {
			$graphic->getUser()->getMeta()->incrementPublicGraphicCount(-1);
		}

		parent::deletePublication($graphic, $withWitness, $flush);
	}

	//////

	public function changeOwner(Graphic $graphic, User $user, $flush = true) {
		parent::changeOwnerPublication($graphic, $user, $flush);
	}

	protected function updateUserCounterAfterChangeOwner(User $user, $by, $isPrivate) {
		if ($isPrivate) {
			$user->getMeta()->incrementPrivateGraphicCount($by);
		} else {
			$user->getMeta()->incrementPublicGraphicCount($by);
		}
	}

}