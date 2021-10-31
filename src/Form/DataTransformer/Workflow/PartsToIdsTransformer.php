<?php

namespace App\Form\DataTransformer\Workflow;

use App\Entity\Workflow\Part;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Doctrine\Persistence\ManagerRegistry;

class PartsToIdsTransformer implements DataTransformerInterface {

	private $om;

	public function __construct(EntityManagerInterface $om) {
		$this->om = $om;
	}

	public function transform($parts) {
		if (null === $parts) {
			return '';
		}

		if (!$parts instanceof \Doctrine\Common\Collections\Collection) {
			throw new UnexpectedTypeException($parts, '\Doctrine\Common\Collections\Collection');
		}

		$idsArray = array();
		foreach ($parts as $part) {
			$idsArray[] = $part->getId();
		}
		return implode(',', $idsArray);
	}

	public function reverseTransform($idsString) {
		if (!$idsString) {
			return array();
		}

		$parts = array();
		$idsStrings = preg_split("/[,]+/", $idsString);
		$repository = $this->om->getRepository(Part::class);
		foreach ($idsStrings as $idString) {
			$id = intval($idString);
			if ($id == 0) {
				continue;
			}
			$part = $repository->find($id);
			if (is_null($part)) {
				throw new TransformationFailedException();
			}
			$parts[] = $part;
		}

		return $parts;
	}

}