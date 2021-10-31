<?php

namespace App\Form\DataTransformer\Workflow;

use App\Entity\Workflow\Label;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class LabelsToIdsTransformer implements DataTransformerInterface {

	private $om;

	public function __construct(EntityManagerInterface $om) {
		$this->om = $om;
	}

	public function transform($labels) {
		if (null === $labels) {
			return '';
		}

		if (!$labels instanceof \Doctrine\Common\Collections\Collection) {
			throw new UnexpectedTypeException($labels, '\Doctrine\Common\Collections\Collection');
		}

		$idsArray = array();
		foreach ($labels as $label) {
			$idsArray[] = $label->getId();
		}
		return implode(',', $idsArray);
	}

	public function reverseTransform($idsString) {
		if (!$idsString) {
			return array();
		}

		$labels = array();
		$idsStrings = preg_split("/[,]+/", $idsString);
		$repository = $this->om->getRepository(Label::class);
		foreach ($idsStrings as $idString) {
			$id = intval($idString);
			if ($id == 0) {
				continue;
			}
			$label = $repository->find($id);
			if (is_null($label)) {
				throw new TransformationFailedException();
			}
			$labels[] = $label;
		}

		return $labels;
	}

}