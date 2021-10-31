<?php

namespace App\Entity\Core\Activity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table("tbl_core_activity_vote")
 * @ORM\Entity(repositoryClass="App\Repository\Core\Activity\VoteRepository")
 */
class Vote extends AbstractActivity {

	const STRIPPED_NAME = 'vote';

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Core\Vote")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $vote;

	/////

	// StrippedName /////

	public function getStrippedName() {
		return self::STRIPPED_NAME;
	}

	// Vote /////

	public function setVote(\App\Entity\Core\Vote $vote) {
		$this->vote = $vote;
		return $this;
	}

	public function getVote() {
		return $this->vote;
	}

}