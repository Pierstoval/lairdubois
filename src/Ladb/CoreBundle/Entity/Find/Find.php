<?php

namespace Ladb\CoreBundle\Entity\Find;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ladb\CoreBundle\Model\BodiedTrait;
use Ladb\CoreBundle\Model\CommentableTrait;
use Ladb\CoreBundle\Model\IndexableTrait;
use Ladb\CoreBundle\Model\LikableTrait;
use Ladb\CoreBundle\Model\PicturedTrait;
use Ladb\CoreBundle\Model\ScrapableTrait;
use Ladb\CoreBundle\Model\SitemapableInterface;
use Ladb\CoreBundle\Model\SitemapableTrait;
use Ladb\CoreBundle\Model\TaggableTrait;
use Ladb\CoreBundle\Model\TitledTrait;
use Ladb\CoreBundle\Model\ViewableTrait;
use Ladb\CoreBundle\Model\WatchableTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Ladb\CoreBundle\Validator\Constraints as LadbAssert;
use Ladb\CoreBundle\Model\JoinableInterface;
use Ladb\CoreBundle\Model\ScrapableInterface;
use Ladb\CoreBundle\Model\IndexableInterface;
use Ladb\CoreBundle\Model\TitledInterface;
use Ladb\CoreBundle\Model\PicturedInterface;
use Ladb\CoreBundle\Model\BodiedInterface;
use Ladb\CoreBundle\Model\ViewableInterface;
use Ladb\CoreBundle\Model\LikableInterface;
use Ladb\CoreBundle\Model\WatchableInterface;
use Ladb\CoreBundle\Model\CommentableInterface;
use Ladb\CoreBundle\Model\ReportableInterface;
use Ladb\CoreBundle\Model\TaggableInterface;
use Ladb\CoreBundle\Model\ExplorableInterface;
use Ladb\CoreBundle\Entity\Find\Content\Event;
use Ladb\CoreBundle\Entity\AbstractAuthoredPublication;

/**
 * @ORM\Table("tbl_find")
 * @ORM\Entity(repositoryClass="Ladb\CoreBundle\Repository\Find\FindRepository")
 * @LadbAssert\UniqueFind()
 */
class Find extends AbstractAuthoredPublication implements TitledInterface, PicturedInterface, BodiedInterface, IndexableInterface, SitemapableInterface, TaggableInterface, ViewableInterface, ScrapableInterface, LikableInterface, WatchableInterface, CommentableInterface, ReportableInterface, ExplorableInterface, JoinableInterface {

	use TitledTrait, PicturedTrait, BodiedTrait;
	use IndexableTrait, SitemapableTrait, TaggableTrait, ViewableTrait, ScrapableTrait, LikableTrait, WatchableTrait, CommentableTrait;

	const CLASS_NAME = 'LadbCoreBundle:Find\Find';
	const TYPE = 104;

	const CONTENT_TYPE_LINK = 0;
	const CONTENT_TYPE_GALLERY = 1;
	const CONTENT_TYPE_EVENT = 2;

	const KIND_NONE = 0;
	const KIND_WEBSITE = 1;
	const KIND_VIDEO = 2;
	const KIND_GALLERY = 3;
	const KIND_EVENT = 4;

	/**
	 * @ORM\Column(type="string", length=100)
	 * @Assert\NotBlank()
	 * @Assert\Length(min=4)
	 * @Assert\Regex("/^[ a-zA-Z0-9ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ'’ʼ#,.:%?!-]+$/", message="default.title.regex")
	 * @ladbAssert\UpperCaseRatio()
	 */
	private $title;

	/**
	 * @Gedmo\Slug(fields={"title"}, separator="-")
	 * @ORM\Column(type="string", length=100, unique=true)
	 */
	private $slug;

	/**
	 * @ORM\OneToOne(targetEntity="Ladb\CoreBundle\Entity\Find\Content\AbstractContent", orphanRemoval=true, cascade={"persist", "remove"})
	 * @ORM\JoinColumn(name="content_id", nullable=false)
	 */
	private $content;

	/**
	 * @ORM\Column(type="smallint")
	 */
	private $kind = Find::KIND_NONE;

	/**
	 * @ORM\Column(type="text", nullable=false)
	 * @Assert\NotBlank()
	 * @Assert\Length(min=5, max=4000)
	 * @LadbAssert\NoMediaLink()
	 */
	private $body;

	/**
	 * @ORM\Column(type="text", nullable=false)
	 */
	private $htmlBody;

	/**
	 * @ORM\ManyToOne(targetEntity="Ladb\CoreBundle\Entity\Picture", cascade={"persist"})
	 * @ORM\JoinColumn(nullable=true, name="main_picture_id")
	 */
	private $mainPicture;

	/**
	 * @ORM\ManyToMany(targetEntity="Ladb\CoreBundle\Entity\Tag", cascade={"persist"})
	 * @ORM\JoinTable(name="tbl_find_tag")
	 * @Assert\Count(min=2)
	 */
	private $tags;

	/**
	 * @ORM\Column(type="integer", name="like_count")
	 */
	private $likeCount = 0;

	/**
	 * @ORM\Column(type="integer", name="watch_count")
	 */
	private $watchCount = 0;

	/**
	 * @ORM\Column(type="integer", name="comment_count")
	 */
	private $commentCount = 0;

	/**
	 * @ORM\Column(type="integer", name="view_count")
	 */
	private $viewCount = 0;

	/**
	 * @ORM\Column(type="integer", name="join_count")
	 */
	private $joinCount = 0;

	/////

	private $contentType = Find::CONTENT_TYPE_LINK;

	/////

	public function __construct() {
		$this->tags = new \Doctrine\Common\Collections\ArrayCollection();
	}

	/////

	// NotificationStrategy /////

	public function getNotificationStrategy() {
		return self::NOTIFICATION_STRATEGY_FOLLOWER;
	}

	// Type /////

	public function getType() {
		return Find::TYPE;
	}

	// DataType /////

	public function setContentType($contentType) {
		$this->contentType = $contentType;
		return $this;
	}

	public function getContentType() {
		return $this->contentType;
	}

	// Slug /////

	public function setSlug($slug) {
		$this->slug = $slug;
		return $this;
	}

	public function getSlug() {
		return $this->slug;
	}

	public function getSluggedId() {
		return $this->id.'-'.$this->slug;
	}

	// Content /////

	public function setContent(\Ladb\CoreBundle\Entity\Find\Content\AbstractContent $content) {
		$this->content = $content;
		return $this;
	}

	public function getContent() {
		return $this->content;
	}

	// Kind /////

	public function setKind($kind) {
		$this->kind = $kind;
		return $this;
	}

	public function getKind() {
		return $this->kind;
	}

	// BodyExtract /////

	public function getBodyExtract() {
		return $this->getHtmlBody();
	}

	// IsJoinable /////

	public function getIsJoinable() {
		return $this->getIsViewable()
			&& $this->getContent() instanceof Event
			&& $this->getContent()->getStatus() != Event::STATUS_COMPLETED;
	}

	// JoinCount /////

	public function incrementJoinCount($by = 1) {
		return $this->joinCount += intval($by);
	}

	public function setJoinCount($joinCount) {
		$this->joinCount = $joinCount;
	}

	public function getJoinCount() {
		return $this->joinCount;
	}

}
