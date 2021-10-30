<?php

namespace App\Entity\Knowledge\Value;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table("tbl_knowledge2_value_isbn")
 * @ORM\Entity(repositoryClass="App\Repository\Knowledge\Value\IsbnRepository")
 */
class Isbn extends BaseValue {

	const TYPE = 19;

	const TYPE_STRIPPED_NAME = 'isbn';

	/**
	 * @ORM\Column(type="string")
	 */
	protected $data;

	/**
	 * @ORM\Column(type="string", length=20)
	 * @Assert\NotBlank(groups={"mandatory"})
	 * @Assert\Isbn
	 * @Assert\Length(max=20)
	 */
	protected $rawIsbn;

	/////

	// Type /////

	public function getType() {
		return self::TYPE;
	}

	// RawIsbn /////

	public function getRawIsbn() {
		return $this->rawIsbn;
	}

	public function setRawIsbn($rawIsbn) {
		$this->rawIsbn = $rawIsbn;
		return $this;
	}

}