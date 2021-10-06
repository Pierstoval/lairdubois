<?php

namespace App\Form\Model;

use App\Entity\Knowledge\Value\Picture;
use App\Entity\Knowledge\Value\Text;

class NewWood {

	/**
	 */
	private $nameValue;

	/**
	 */
	private $grainValue;

	// NameValue /////

	public function setNameValue(Text $nameValue) {
		$this->nameValue = $nameValue;
		return $this;
	}

	public function getNameValue() {
		return $this->nameValue;
	}

	// GrainValue /////

	public function setGrainValue(Picture $grainValue) {
		$this->grainValue = $grainValue;
		return $this;
	}

	public function getGrainValue() {
		return $this->grainValue;
	}

}