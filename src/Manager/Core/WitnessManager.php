<?php

namespace App\Manager\Core;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\AbstractPublication;
use App\Entity\Core\Witness;
use App\Utils\TypableUtils;
use App\Manager\AbstractManager;

class WitnessManager extends AbstractManager {

	public function deleteByPublication(AbstractPublication $publication, $flush = true) {
		$om = $this->getDoctrine()->getManager();
		$witnessRepository = $om->getRepository(Witness::class);

		$witness = $witnessRepository->findOneByEntityTypeAndEntityId($publication->getType(), $publication->getId());
		if (!is_null($witness)) {

			$om->remove($witness);

			if ($flush) {
				$om->flush();
			}

			return true;
		}

		return false;
	}

	public function createUnpublishedByPublication(AbstractPublication $publication, $reason = null, $flush = true) {

		$witness = $this->createByPublication($publication);
		$witness->setKind(Witness::KIND_UNPUBLISHED);
		$witness->setMeta($reason);

		if ($flush) {
			$om = $this->getDoctrine()->getManager();
			$om->flush();
		}

		return $witness;
	}

	public function createByPublication(AbstractPublication $publication, $flush = true) {
		$om = $this->getDoctrine()->getManager();
		$witnessRepository = $om->getRepository(Witness::class);

		// Remove if it exists
		$witness = $witnessRepository->findOneByEntityTypeAndEntityId($publication->getType(), $publication->getId());
		if (!is_null($witness)) {
			$om->remove($witness);
		}

		$witness = new Witness();
		$witness->setEntityType($publication->getType());
		$witness->setEntityId($publication->getId());

		$om->persist($witness);

		if ($flush) {
			$om->flush();
		}

		return $witness;
	}

	public function createConvertedByPublication(AbstractPublication $publication, AbstractPublication $toPublication, $flush = true) {

		$witness = $this->createByPublication($publication);
		$witness->setKind(Witness::KIND_CONVERTED);
		$witness->setMeta(array( $toPublication->getType(), $toPublication->getId() ));

		if ($flush) {
			$om = $this->getDoctrine()->getManager();
			$om->flush();
		}

		return $witness;
	}

	public function createDeletedByPublication(AbstractPublication $publication, $reason = null, $flush = true) {

		$witness = $this->createByPublication($publication);
		$witness->setKind(Witness::KIND_DELETED);
		$witness->setMeta($reason);

		if ($flush) {
			$om = $this->getDoctrine()->getManager();
			$om->flush();
		}

		return $witness;
	}

	/////

	public function checkResponse($entityType, $entityId) {
		$om = $this->getDoctrine()->getManager();
		$witnessRepository = $om->getRepository(Witness::class);

		$witness = $witnessRepository->findOneByEntityTypeAndEntityId($entityType, $entityId);
		if (!is_null($witness)) {

			switch ($witness->getKind()) {

				case Witness::KIND_UNPUBLISHED:
					throw new GoneHttpException('Unpublished entity (type='.$entityType.' id='.$entityId.').');

				case Witness::KIND_CONVERTED:
					if (is_null($witness->getMeta()) || !is_array($witness->getMeta()) && count($witness->getMeta()) < 2) {
						throw new NotFoundHttpException('Unable to redirect (no or bad meta).');
					}
					$meta = $witness->getMeta();
					$entityType = $meta[0];
					$entityId = $meta[1];
					$typableUtils = $this->get(TypableUtils::class);
					$typable = $typableUtils->findTypable($entityType, $entityId);
					if (is_null($typable)) {
						throw new NotFoundHttpException('Unable to find entity (type='.$entityType.' id='.$entityId.').');
					}
					return new RedirectResponse($typableUtils->getUrlAction($typable), 301);	// 301 = Moved Permanently

				case Witness::KIND_DELETED:
					throw new GoneHttpException('Deleted entity (type='.$entityType.' id='.$entityId.').');

			}

		}

		return null;
	}

}