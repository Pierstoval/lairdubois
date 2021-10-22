<?php

namespace App\Form\DataTransformer;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use App\Entity\Core\Picture;

class PictureToIdTransformer implements DataTransformerInterface {

	private $om;

	public function __construct(ManagerRegistry $om) {
		$this->om = $om;
	}

	public function transform($picture) {
		if (null === $picture) {
			return '';
		}

		if (!$picture instanceof \App\Entity\Core\Picture) {
			throw new UnexpectedTypeException($picture, '\App\Entity\Core\Picture');
		}

		return $picture->getId();
	}

	public function reverseTransform($idString) {
		if (!$idString) {
			return null;
		}

		$id = intval($idString);
		if ($id == 0) {
			throw new TransformationFailedException();
		}
		$picture = $this->om
			->getRepository(Picture::CLASS_NAME)
			->find($id);
		if (is_null($picture)) {
			throw new TransformationFailedException();
		}

		return $picture;
	}

}