<?php

namespace App\Utils;

use App\Entity\Core\Watch;
use App\Model\WatchableInterface;
use App\Entity\Core\User;

class WatchableUtils extends AbstractContainerAwareUtils {

    public function autoCreateWatch(WatchableInterface $watchable, User $user) {
        if ($user->getMeta()->getAutoWatchEnabled()) {
            return $this->createWatch($watchable, $user);
        }
        return false;
    }

    public function createWatch(WatchableInterface $watchable, User $user) {
		$om = $this->getDoctrine()->getManager();
		$watchRepository = $om->getRepository(Watch::class);

		if (!$watchRepository->existsByEntityTypeAndEntityIdAndUser($watchable->getType(), $watchable->getId(), $user)) {

            $watchable->incrementWatchCount();

            $watch = new Watch();
            $watch->setEntityType($watchable->getType());
            $watch->setEntityId($watchable->getId());
            $watch->setUser($user);

			$om->persist($watch);
			$om->flush();

            return true;
        }
        return false;
    }

	public function deleteWatches(WatchableInterface $watchable, $flush = true) {
		$om = $this->getDoctrine()->getManager();
		$watchRepository = $om->getRepository(Watch::class);

		$watches = $watchRepository->findByEntityTypeAndEntityId($watchable->getType(), $watchable->getId());
		foreach ($watches as $watch) {
			$om->remove($watch);
		}
		if ($flush) {
			$om->flush();
		}
	}

	/////

	public function getWatchContext(WatchableInterface $watchable, User $user = null) {
		$om = $this->getDoctrine()->getManager();
		$watchRepository = $om->getRepository(Watch::class);
		$watch = null;

		if (!is_null($user)) {
			$watch = $watchRepository->findOneByEntityTypeAndEntityIdAndUser($watchable->getType(), $watchable->getId(), $user);
		}
		return array(
			'id'         => is_null($watch) ? null : $watch->getId(),
			'entityType' => $watchable->getType(),
			'entityId'   => $watchable->getId(),
			'count'      => $watchable->getWatchCount(),
		);
	}

	/////

	public function transferWatches(WatchableInterface $watchableSrc, WatchableInterface $watchableDest, $flush = true) {
		$om = $this->getDoctrine()->getManager();
		$watchRepository = $om->getRepository(Watch::class);

		// Retrieve watches
		$watches = $watchRepository->findByEntityTypeAndEntityId($watchableSrc->getType(), $watchableSrc->getId());

		// Transfer watches
		foreach ($watches as $watch) {
			$watch->setEntityType($watchableDest->getType());
			$watch->setEntityId($watchableDest->getId());
		}

		// Update counters
		$watchableDest->incrementWatchCount($watchableSrc->getWatchCount());
		$watchableSrc->setWatchCount(0);

		if ($flush) {
			$om->flush();
		}
	}

}