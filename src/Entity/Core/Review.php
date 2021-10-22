<?php

namespace App\Entity\Core;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as LadbAssert;
use App\Model\TypableInterface;
use App\Model\AuthoredTrait;
use App\Model\TitledInterface;
use App\Model\TitledTrait;
use App\Model\HtmlBodiedTrait;
use App\Model\HtmlBodiedInterface;
use App\Model\AuthoredInterface;
use App\Model\BasicTimestampableInterface;
use App\Model\BasicTimestampableTrait;

/**
 * @ORM\Table("tbl_core_review",
 *		uniqueConstraints={
 *			@ORM\UniqueConstraint(name="ENTITY_USER_UNIQUE", columns={"entity_type", "entity_id", "user_id"})
 * 		},
 * 		indexes={
 *     		@ORM\Index(name="IDX_REVIEW_ENTITY", columns={"entity_type", "entity_id"})
 * 		})
 * @ORM\Entity(repositoryClass="App\Repository\Core\ReviewRepository")
 */
class Review implements TypableInterface, BasicTimestampableInterface, AuthoredInterface, TitledInterface, HtmlBodiedInterface {

	use BasicTimestampableTrait;
	use AuthoredTrait, TitledTrait, HtmlBodiedTrait;

	const CLASS_NAME = 'App\Entity\Core\Review';
	const TYPE = 4;

	/**
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @ORM\Column(name="entity_type", type="smallint", nullable=false)
	 */
	private $entityType;

	/**
	 * @ORM\Column(name="entity_id", type="integer", nullable=false)
	 */
	private $entityId;

	/**
	 * @ORM\Column(name="created_at", type="datetime")
	 * @Gedmo\Timestampable(on="create")
	 */
	protected $createdAt;

	/**
	 * @ORM\Column(name="updated_at", type="datetime")
	 * @Gedmo\Timestampable(on="update")
	 */
	private $updatedAt;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Core\User")
	 * @ORM\JoinColumn(nullable=false)
	 */
	private $user;

	/**
	 * @ORM\Column(type="string", length=100, nullable=false)
	 * @Assert\NotBlank()
	 * @Assert\Length(min=2, max=100)
	 */
	protected $title;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 * @Assert\NotBlank()
	 * @Assert\Length(min=5, max=5000)
	 * @LadbAssert\NoMediaLink()
	 */
	protected $body;

	/**
	 * @ORM\Column(name="html_body", type="text", nullable=true)
	 */
	private $htmlBody;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	private $rating;

	/////

	// Id /////

	public function getId() {
		return $this->id;
	}

	// Type /////

	public function getType() {
		return self::TYPE;
	}

	// EntityType /////

	public function setEntityType($entityType) {
		$this->entityType = $entityType;
	}

	public function getEntityType() {
		return $this->entityType;
	}

	// EntityId /////

	public function setEntityId($entityId) {
		$this->entityId = $entityId;
		return $this;
	}

	public function getEntityId() {
		return $this->entityId;
	}

	// Rating /////

	public function setRating($rating) {
		$this->rating = $rating;
		return $this;
	}

	public function getRating() {
		return $this->rating;
	}

}