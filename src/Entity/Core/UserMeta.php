<?php

namespace App\Entity\Core;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table("tbl_core_user_meta")
 * @ORM\Entity
 */
class UserMeta {

	const CLASS_NAME = 'App\Entity\Core\UserMeta';

	/**
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;


	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Core\Picture", cascade={"persist", "remove"})
	 * @ORM\JoinColumn(name="banner_id", nullable=true)
	 * @Assert\Type(type="App\Entity\Core\Picture")
	 */
	private $banner;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Entity\Core\Biography", cascade={"persist", "remove"})
	 * @ORM\JoinColumn(nullable=true, name="biography_id")
	 * @Assert\Type(type="App\Entity\Core\Biography")
	 */
	private $biography;

	/**
	 * @ORM\ManyToMany(targetEntity="App\Entity\Input\Skill", cascade={"persist"})
	 * @ORM\JoinTable(name="tbl_core_user_meta_skill")
	 */
	private $skills;


	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 * @Assert\Url()
	 */
	private $website;

	/**
	 * @ORM\Column(type="string", length=50, nullable=true)
	 * @Assert\Regex("/^[a-zA-Z0-9.]+$/")
	 */
	private $facebook;

	/**
	 * @ORM\Column(type="string", length=50, nullable=true)
	 * @Assert\Regex("/^[a-zA-Z0-9_]{1,15}$/")
	 */
	private $twitter;

	/**
	 * @ORM\Column(type="string", length=50, nullable=true)
	 * @Assert\Regex("/^[a-zA-Z0-9-_]+$/")
	 */
	private $youtube;

	/**
	 * @ORM\Column(type="string", length=24, nullable=true)
	 * @Assert\Regex("/^[a-zA-Z0-9]+$/")
	 */
	private $vimeo;

	/**
	 * @ORM\Column(type="string", length=24, nullable=true)
	 * @Assert\Regex("/^[a-zA-Z0-9]+$/")
	 */
	private $dailymotion;

	/**
	 * @ORM\Column(type="string", length=50, nullable=true)
	 * @Assert\Regex("/^[a-zA-Z0-9]+$/")
	 */
	private $pinterest;

	/**
	 * @ORM\Column(type="string", length=50, nullable=true)
	 * @Assert\Regex("/^[a-zA-Z0-9_.]+$/")
	 */
	private $instagram;


	/**
	 * @ORM\Column(type="boolean", nullable=true, name="request_enabled", options={"default":true})
	 */
	private $requestEnabled = false;

	/**
	 * @ORM\Column(type="boolean", nullable=true, name="auto_watch_enabled", options={"default":true})
	 */
	private $autoWatchEnabled = true;


	/**
	 * @ORM\Column(type="boolean", name="wonder_creations_badge_enabled", options={"default":true})
	 */
	private $wonderCreationsBadgeEnabled = true;

	/**
	 * @ORM\Column(type="boolean", name="qa_questions_badge_enabled", options={"default":true})
	 */
	private $qaQuestionsBadgeEnabled = true;

	/**
	 * @ORM\Column(type="boolean", name="wonder_plans_badge_enabled", options={"default":true})
	 */
	private $wonderPlansBadgeEnabled = true;

	/**
	 * @ORM\Column(type="boolean", name="howto_howtos_badge_enabled", options={"default":true})
	 */
	private $howtoHowtosBadgeEnabled = true;

	/**
	 * @ORM\Column(type="boolean", name="wonder_workshops_badge_enabled", options={"default":true})
	 */
	private $wonderWorkshopsBadgeEnabled = true;

	/**
	 * @ORM\Column(type="boolean", name="knowledge_woods_badge_enabled", options={"default":true})
	 */
	private $knowledgeWoodsBadgeEnabled = true;

	/**
	 * @ORM\Column(type="boolean", name="knowledge_tools_badge_enabled", options={"default":true})
	 */
	private $knowledgeToolsBadgeEnabled = true;

	/**
	 * @ORM\Column(type="boolean", name="knowledge_books_badge_enabled", options={"default":true})
	 */
	private $knowledgeBooksBadgeEnabled = true;

	/**
	 * @ORM\Column(type="boolean", name="knowledge_softwares_badge_enabled", options={"default":true})
	 */
	private $knowledgeSoftwaresBadgeEnabled = true;

	/**
	 * @ORM\Column(type="boolean", name="collection_collections_badge_enabled", options={"default":true})
	 */
	private $collectionCollectionsBadgeEnabled = true;

	/**
	 * @ORM\Column(type="boolean", name="knowledgeproviders_badge_enabled", options={"default":true})
	 */
	private $knowledgeProvidersBadgeEnabled = true;

	/**
	 * @ORM\Column(type="boolean", name="knowledge_schools_badge_enabled", options={"default":true})
	 */
	private $knowledgeSchoolsBadgeEnabled = true;

	/**
	 * @ORM\Column(type="boolean", name="find_finds_badge_enabled", options={"default":true})
	 */
	private $findFindsBadgeEnabled = true;

	/**
	 * @ORM\Column(type="boolean", name="event_events_badge_enabled", options={"default":true})
	 */
	private $eventEventsBadgeEnabled = true;

	/**
	 * @ORM\Column(type="boolean", name="offer_offers_badge_enabled", options={"default":true})
	 */
	private $offerOffersBadgeEnabled = true;

	/**
	 * @ORM\Column(type="boolean", name="workflow_workflows_badge_enabled", options={"default":true})
	 */
	private $workflowWorkflowsBadgeEnabled = true;

	/**
	 * @ORM\Column(type="boolean", name="promotion_graphics_badge_enabled", options={"default":true})
	 */
	private $promotionGraphicsBadgeEnabled = true;

	/**
	 * @ORM\Column(type="boolean", name="blog_posts_badge_enabled", options={"default":true})
	 */
	private $blogPostsBadgeEnabled = true;

	/**
	 * @ORM\Column(type="boolean", name="faq_questions_badge_enabled", options={"default":true})
	 */
	private $faqQuestionsBadgeEnabled = true;


	/**
	 * @ORM\Column(type="boolean", nullable=true, name="incoming_message_email_notification_enabled", options={"default":true})
	 */
	private $incomingMessageEmailNotificationEnabled = true;

	/**
	 * @ORM\Column(type="boolean", nullable=true, name="new_follower_email_notification_enabled", options={"default":true})
	 */
	private $newFollowerEmailNotificationEnabled = true;

	/**
	 * @ORM\Column(type="boolean", nullable=true, name="new_mention_email_notification_enabled", options={"default":true})
	 */
	private $newMentionEmailNotificationEnabled = true;

	/**
	 * @ORM\Column(type="boolean", nullable=true, name="new_like_email_notification_enabled", options={"default":true})
	 */
	private $newLikeEmailNotificationEnabled = true;

	/**
	 * @ORM\Column(type="boolean", nullable=true, name="new_vote_email_notification_enabled", options={"default":true})
	 */
	private $newVoteEmailNotificationEnabled = true;

	/**
	 * @ORM\Column(type="boolean", nullable=true, name="new_following_post_email_notification_enabled", options={"default":true})
	 */
	private $newFollowingPostEmailNotificationEnabled = true;

	/**
	 * @ORM\Column(type="boolean", nullable=true, name="new_watch_activity_email_notification_enabled", options={"default":true})
	 */
	private $newWatchActivityEmailNotificationEnabled = true;

	/**
	 * @ORM\Column(type="boolean", nullable=true, name="new_spotlight_email_notification_enabled", options={"default":true})
	 */
	private $newSpotlightEmailNotificationEnabled = true;

	/**
	 * @ORM\Column(type="boolean", nullable=true, name="week_news_email_enabled", options={"default":true})
	 */
	private $weekNewsEmailEnabled = true;


	/**
	 * @ORM\Column(name="unlisted_wonder_creation_count", type="integer")
	 */
	private $unlistedWonderCreationCount = 0;

	/**
	 * @ORM\Column(name="unlisted_wonder_plan_count", type="integer")
	 */
	private $unlistedWonderPlanCount = 0;

	/**
	 * @ORM\Column(name="unlisted_wonder_workshop_count", type="integer")
	 */
	private $unlistedWonderWorkshopCount = 0;

	/**
	 * @ORM\Column(name="unlisted_find_find_count", type="integer")
	 */
	private $unlistedFindFindCount = 0;

	/**
	 * @ORM\Column(name="unlisted_event_event_count", type="integer")
	 */
	private $unlistedEventEventCount = 0;

	/**
	 * @ORM\Column(name="unlisted_howto_howto_count", type="integer")
	 */
	private $unlistedHowtoHowtoCount = 0;

	/**
	 * @ORM\Column(name="unlisted_knowledge_wood_count", type="integer")
	 */
	private $unlistedKnowledgeWoodCount = 0;

	/**
	 * @ORM\Column(name="unlisted_knowledge_provider_count", type="integer")
	 */
	private $unlistedKnowledgeProviderCount = 0;

	/**
	 * @ORM\Column(name="unlisted_knowledge_school_count", type="integer")
	 */
	private $unlistedKnowledgeSchoolCount = 0;

	/**
	 * @ORM\Column(name="unlisted_knowledge_book_count", type="integer")
	 */
	private $unlistedKnowledgeBookCount = 0;

	/**
	 * @ORM\Column(name="unlisted_knowledge_software_count", type="integer")
	 */
	private $unlistedKnowledgeSoftwareCount = 0;

	/**
	 * @ORM\Column(name="unlisted_knowledge_tool_count", type="integer")
	 */
	private $unlistedKnowledgeToolCount = 0;

	/**
	 * @ORM\Column(name="unlisted_blog_post_count", type="integer")
	 */
	private $unlistedBlogPostCount = 0;

	/**
	 * @ORM\Column(name="unlisted_faq_question_count", type="integer")
	 */
	private $unlistedFaqQuestionCount = 0;

	/**
	 * @ORM\Column(name="unlisted_qa_question_count", type="integer")
	 */
	private $unlistedQaQuestionCount = 0;

	/**
	 * @ORM\Column(name="unlisted_promotion_graphic_count", type="integer")
	 */
	private $unlistedPromotionGraphicCount = 0;

	/**
	 * @ORM\Column(name="unlisted_workflow_workflow_count", type="integer")
	 */
	private $unlistedWorkflowWorkflowCount = 0;

	/**
	 * @ORM\Column(name="unlisted_collection_collection_count", type="integer")
	 */
	private $unlistedCollectionCollectionCount = 0;

	/**
	 * @ORM\Column(name="unlisted_offer_offer_count", type="integer")
	 */
	private $unlistedOfferOfferCount = 0;


	/**
	 * @ORM\Column(type="integer", nullable=true, name="member_count")
	 */
	private $memberCount = 0;

	/**
	 * @ORM\Column(type="integer", nullable=true, name="invitation_count")
	 */
	private $invitationCount = 0;

	/**
	 * @ORM\Column(type="integer", nullable=true, name="request_count")
	 */
	private $requestCount = 0;

	/**
	 * @ORM\Column(type="integer", nullable=true, name="team_count")
	 */
	private $teamCount = 0;


	/**
	 * @ORM\Column(type="integer", nullable=true, name="follower_count")
	 */
	private $followerCount = 0;

	/**
	 * @ORM\Column(type="integer", nullable=true, name="following_count")
	 */
	private $followingCount = 0;


	/**
	 * @ORM\Column(type="integer", nullable=true, name="recieved_like_count")
	 */
	private $recievedLikeCount = 0;

	/**
	 * @ORM\Column(type="integer", nullable=true, name="sent_like_count")
	 */
	private $sentLikeCount = 0;


	/**
	 * @ORM\Column(type="integer", nullable=true, name="positive_vote_count")
	 */
	private $positiveVoteCount = 0;

	/**
	 * @ORM\Column(type="integer", nullable=true, name="negative_vote_count")
	 */
	private $negativeVoteCount = 0;


	/**
	 * @ORM\Column(type="integer", nullable=true, name="unread_message_count")
	 */
	private $unreadMessageCount = 0;

	/**
	 * @ORM\Column(type="integer", nullable=true, name="fresh_notification_count")
	 */
	private $freshNotificationCount = 0;

	/**
	 * @ORM\Column(type="datetime", nullable=true, name="notifications_folding_since")
	 */
	private $notificationsFoldingSince;


	/**
	 * @ORM\Column(type="integer", nullable=true, name="comment_count")
	 */
	private $commentCount = 0;


	/**
	 * @ORM\Column(type="integer", nullable=true, name="contribution_count")
	 */
	private $contributionCount = 0;


	/**
	 * @ORM\Column(type="integer", nullable=true, name="private_creation_count")
	 */
	private $privateCreationCount = 0;

	/**
	 * @ORM\Column(type="integer", nullable=true, name="public_creation_count")
	 */
	private $publicCreationCount = 0;


	/**
	 * @ORM\Column(type="integer", nullable=true, name="private_plan_count")
	 */
	private $privatePlanCount = 0;

	/**
	 * @ORM\Column(type="integer", nullable=true, name="public_plan_count")
	 */
	private $publicPlanCount = 0;


	/**
	 * @ORM\Column(type="integer", nullable=true, name="private_howto_count")
	 */
	private $privateHowtoCount = 0;

	/**
	 * @ORM\Column(type="integer", nullable=true, name="public_howto_count")
	 */
	private $publicHowtoCount = 0;


	/**
	 * @ORM\Column(type="integer", nullable=true, name="private_workshop_count")
	 */
	private $privateWorkshopCount = 0;

	/**
	 * @ORM\Column(type="integer", nullable=true, name="public_workshop_count")
	 */
	private $publicWorkshopCount = 0;


	/**
	 * @ORM\Column(type="integer", nullable=true, name="private_find_count")
	 */
	private $privateFindCount = 0;

	/**
	 * @ORM\Column(type="integer", nullable=true, name="public_find_count")
	 */
	private $publicFindCount = 0;


	/**
	 * @ORM\Column(type="integer", nullable=true, name="private_event_count")
	 */
	private $privateEventCount = 0;

	/**
	 * @ORM\Column(type="integer", nullable=true, name="public_event_count")
	 */
	private $publicEventCount = 0;


	/**
	 * @ORM\Column(type="integer", nullable=true, name="private_question_count")
	 */
	private $privateQuestionCount = 0;

	/**
	 * @ORM\Column(type="integer", nullable=true, name="public_question_count")
	 */
	private $publicQuestionCount = 0;

	/**
	 * @ORM\Column(type="integer", nullable=true, name="answer_count")
	 */
	private $answerCount = 0;


	/**
	 * @ORM\Column(type="integer", nullable=true, name="private_offer_count")
	 */
	private $privateOfferCount = 0;

	/**
	 * @ORM\Column(type="integer", nullable=true, name="public_offer_count")
	 */
	private $publicOfferCount = 0;


	/**
	 * @ORM\Column(type="integer", nullable=true, name="private_graphic_count")
	 */
	private $privateGraphicCount = 0;

	/**
	 * @ORM\Column(type="integer", nullable=true, name="public_graphic_count")
	 */
	private $publicGraphicCount = 0;


	/**
	 * @ORM\Column(type="integer", nullable=true, name="private_workflow_count")
	 */
	private $privateWorkflowCount = 0;

	/**
	 * @ORM\Column(type="integer", nullable=true, name="public_workflow_count")
	 */
	private $publicWorkflowCount = 0;


	/**
	 * @ORM\Column(type="integer", nullable=true, name="private_collection_count")
	 */
	private $privateCollectionCount = 0;

	/**
	 * @ORM\Column(type="integer", nullable=true, name="public_collection_count")
	 */
	private $publicCollectionCount = 0;


	/**
	 * @ORM\Column(type="integer", nullable=true, name="proposal_count")
	 */
	private $proposalCount = 0;

	/**
	 * @ORM\Column(type="integer", nullable=true, name="testimonial_count")
	 */
	private $testimonialCount = 0;

	/**
	 * @ORM\Column(type="integer", nullable=true, name="review_count")
	 */
	private $reviewCount = 0;

	/**
	 * @ORM\Column(type="integer", nullable=true, name="feedback_count")
	 */
	private $feedbackCount = 0;


	/**
	 * @ORM\Column(name="donation_count", type="integer")
	 */
	private $donationCount = 0;

	/**
	 * @ORM\Column(name="donation_balance", type="integer")
	 */
	private $donationBalance = 0;

	/**
	 * @ORM\Column(name="donation_fee_balance", type="integer")
	 */
	private $donationFeeBalance = 0;


	/////

	public function __construct() {
		$this->skills = new \Doctrine\Common\Collections\ArrayCollection();
	}

	/////

	// Id /////

	public function getId() {
		return $this->id;
	}


	// Banner /////

	public function setBanner(\App\Entity\Core\Picture $banner = null) {
		$this->banner = $banner;
		return $this;
	}

	public function getBanner() {
		return $this->banner;
	}


	// Biography /////

	public function setBiography(\App\Entity\Core\Biography $biography = null) {
		$this->biography = $biography;
	}

	public function getBiography() {
		return $this->biography;
	}

	// Skills /////

	public function addSkill(\App\Entity\Input\Skill $skill) {
		$this->skills[] = $skill;
		return $this;
	}

	public function removeSkill(\App\Entity\Input\Skill $skill) {
		$this->skills->removeElement($skill);
	}

	public function getSkills() {
		return $this->skills;
	}


	// Website /////

	public function setWebsite($website) {
		$this->website = $website;
	}

	public function getWebsite() {
		return $this->website;
	}

	// Facebook /////

	public function setFacebook($facebook) {
		$this->facebook = $facebook;
	}

	public function getFacebook() {
		return $this->facebook;
	}

	// Twitter /////

	public function setTwitter($twitter) {
		$this->twitter = $twitter;
	}

	public function getTwitter() {
		return $this->twitter;
	}

	// YouTube /////

	public function setYoutube($youtube) {
		$this->youtube = $youtube;
	}

	public function getYoutube() {
		return $this->youtube;
	}

	// Vimeo /////

	public function setVimeo($vimeo) {
		$this->vimeo = $vimeo;
	}

	public function getVimeo() {
		return $this->vimeo;
	}

	// Dailymotion /////

	public function setDailymotion($dailymotion) {
		$this->dailymotion = $dailymotion;
	}

	public function getDailymotion() {
		return $this->dailymotion;
	}

	// Pinterest /////

	public function setPinterest($pinterest) {
		$this->pinterest = $pinterest;
	}

	public function getPinterest() {
		return $this->pinterest;
	}

	// Instagram /////

	public function setInstagram($instagram) {
		$this->instagram = $instagram;
	}

	public function getInstagram() {
		return $this->instagram;
	}


	// RequestEnabled /////

	public function setRequestEnabled($requestEnabled) {
		$this->requestEnabled = $requestEnabled;
		return $this;
	}

	public function getRequestEnabled() {
		return $this->requestEnabled;
	}

	// AutoWatchEnabled /////

	public function setAutoWatchEnabled($autoWatchEnabled) {
		$this->autoWatchEnabled = $autoWatchEnabled;
		return $this;
	}

	public function getAutoWatchEnabled() {
		return $this->autoWatchEnabled;
	}


	// WonderCreationsBadgeEnabled /////

	public function setWonderCreationsBadgeEnabled($wonderCreationsBadgeEnabled) {
		$this->wonderCreationsBadgeEnabled = $wonderCreationsBadgeEnabled;
		return $this;
	}

	public function getWonderCreationsBadgeEnabled() {
		return $this->wonderCreationsBadgeEnabled;
	}

	// QaQuestionsBadgeEnabled /////

	public function setQaQuestionsBadgeEnabled($qaQuestionsBadgeEnabled) {
		$this->qaQuestionsBadgeEnabled = $qaQuestionsBadgeEnabled;
		return $this;
	}

	public function getQaQuestionsBadgeEnabled() {
		return $this->qaQuestionsBadgeEnabled;
	}

	// WonderPlansBadgeEnabled /////

	public function setWonderPlansBadgeEnabled($wonderPlansBadgeEnabled) {
		$this->wonderPlansBadgeEnabled = $wonderPlansBadgeEnabled;
		return $this;
	}

	public function getWonderPlansBadgeEnabled() {
		return $this->wonderPlansBadgeEnabled;
	}

	// HowtoHowtosBadgeEnabled /////

	public function setHowtoHowtosBadgeEnabled($howtoHowtosBadgeEnabled) {
		$this->howtoHowtosBadgeEnabled = $howtoHowtosBadgeEnabled;
		return $this;
	}

	public function getHowtoHowtosBadgeEnabled() {
		return $this->howtoHowtosBadgeEnabled;
	}

	// WonderWorkshopsBadgeEnabled /////

	public function setWonderWorkshopsBadgeEnabled($wonderWorkshopsBadgeEnabled) {
		$this->wonderWorkshopsBadgeEnabled = $wonderWorkshopsBadgeEnabled;
		return $this;
	}

	public function getWonderWorkshopsBadgeEnabled() {
		return $this->wonderWorkshopsBadgeEnabled;
	}

	// KnowledgeWoodsBadgeEnabled /////

	public function setKnowledgeWoodsBadgeEnabled($knowledgeWoodsBadgeEnabled) {
		$this->knowledgeWoodsBadgeEnabled = $knowledgeWoodsBadgeEnabled;
		return $this;
	}

	public function getKnowledgeWoodsBadgeEnabled() {
		return $this->knowledgeWoodsBadgeEnabled;
	}

	// KnowledgeToolsBadgeEnabled /////

	public function setKnowledgeToolsBadgeEnabled($knowledgeToolsBadgeEnabled) {
		$this->knowledgeToolsBadgeEnabled = $knowledgeToolsBadgeEnabled;
		return $this;
	}

	public function getKnowledgeToolsBadgeEnabled() {
		return $this->knowledgeToolsBadgeEnabled;
	}

	// KnowledgeBooksBadgeEnabled /////

	public function setKnowledgeBooksBadgeEnabled($knowledgeBooksBadgeEnabled) {
		$this->knowledgeBooksBadgeEnabled = $knowledgeBooksBadgeEnabled;
		return $this;
	}

	public function getKnowledgeBooksBadgeEnabled() {
		return $this->knowledgeBooksBadgeEnabled;
	}

	// KnowledgeSoftwaresBadgeEnabled /////

	public function setKnowledgeSoftwaresBadgeEnabled($knowledgeSoftwaresBadgeEnabled) {
		$this->knowledgeSoftwaresBadgeEnabled = $knowledgeSoftwaresBadgeEnabled;
		return $this;
	}

	public function getKnowledgeSoftwaresBadgeEnabled() {
		return $this->knowledgeSoftwaresBadgeEnabled;
	}

	// CollectionCollectionsBadgeEnabled /////

	public function setCollectionCollectionsBadgeEnabled($collectionCollectionsBadgeEnabled) {
		$this->collectionCollectionsBadgeEnabled = $collectionCollectionsBadgeEnabled;
		return $this;
	}

	public function getCollectionCollectionsBadgeEnabled() {
		return $this->collectionCollectionsBadgeEnabled;
	}

	// KnowledgeProvidersBadgeEnabled /////

	public function setKnowledgeProvidersBadgeEnabled($knowledgeProvidersBadgeEnabled) {
		$this->knowledgeProvidersBadgeEnabled = $knowledgeProvidersBadgeEnabled;
		return $this;
	}

	public function getKnowledgeProvidersBadgeEnabled() {
		return $this->knowledgeProvidersBadgeEnabled;
	}

	// SchoolSchoolsBadgeEnabled /////

	public function setKnowledgeSchoolsBadgeEnabled($knowledgeSchoolsBadgeEnabled) {
		$this->knowledgeSchoolsBadgeEnabled = $knowledgeSchoolsBadgeEnabled;
		return $this;
	}

	public function getKnowledgeSchoolsBadgeEnabled() {
		return $this->knowledgeSchoolsBadgeEnabled;
	}

	// FindFindsBadgeEnabled /////

	public function setFindFindsBadgeEnabled($findFindsBadgeEnabled) {
		$this->findFindsBadgeEnabled = $findFindsBadgeEnabled;
		return $this;
	}

	public function getFindFindsBadgeEnabled() {
		return $this->findFindsBadgeEnabled;
	}

	// EventEventsBadgeEnabled /////

	public function setEventEventsBadgeEnabled($eventEventsBadgeEnabled) {
		$this->eventEventsBadgeEnabled = $eventEventsBadgeEnabled;
		return $this;
	}

	public function getEventEventsBadgeEnabled() {
		return $this->eventEventsBadgeEnabled;
	}

	// OfferOffersBadgeEnabled /////

	public function setOfferOffersBadgeEnabled($offerOffersBadgeEnabled) {
		$this->offerOffersBadgeEnabled = $offerOffersBadgeEnabled;
		return $this;
	}

	public function getOfferOffersBadgeEnabled() {
		return $this->offerOffersBadgeEnabled;
	}

	// WorkflowWorkflowsBadgeEnabled /////

	public function setWorkflowWorkflowsBadgeEnabled($workflowWorkflowsBadgeEnabled) {
		$this->workflowWorkflowsBadgeEnabled = $workflowWorkflowsBadgeEnabled;
		return $this;
	}

	public function getWorkflowWorkflowsBadgeEnabled() {
		return $this->workflowWorkflowsBadgeEnabled;
	}

	// PromotionGraphicsBadgeEnabled /////

	public function setPromotionGraphicsBadgeEnabled($promotionGraphicsBadgeEnabled) {
		$this->promotionGraphicsBadgeEnabled = $promotionGraphicsBadgeEnabled;
		return $this;
	}

	public function getPromotionGraphicsBadgeEnabled() {
		return $this->promotionGraphicsBadgeEnabled;
	}

	// BlogPostsBadgeEnabled /////

	public function setBlogPostsBadgeEnabled($blogPostsBadgeEnabled) {
		$this->blogPostsBadgeEnabled = $blogPostsBadgeEnabled;
		return $this;
	}

	public function getBlogPostsBadgeEnabled() {
		return $this->blogPostsBadgeEnabled;
	}

	// FaqQuestionsBadgeEnabled /////

	public function setFaqQuestionsBadgeEnabled($faqQuestionsBadgeEnabled) {
		$this->faqQuestionsBadgeEnabled = $faqQuestionsBadgeEnabled;
		return $this;
	}

	public function getFaqQuestionsBadgeEnabled() {
		return $this->faqQuestionsBadgeEnabled;
	}


	// IncomingMessageEmailNotificationEnabled /////

	public function setIncomingMessageEmailNotificationEnabled($incomingMessageEmailNotificationEnabled) {
		$this->incomingMessageEmailNotificationEnabled = $incomingMessageEmailNotificationEnabled;
		return $this;
	}

	public function getIncomingMessageEmailNotificationEnabled() {
		return $this->incomingMessageEmailNotificationEnabled;
	}

	// NewFollowerEmailNotificationEnabled /////

	public function setNewFollowerEmailNotificationEnabled($newFollowerEmailNotificationEnabled) {
		$this->newFollowerEmailNotificationEnabled = $newFollowerEmailNotificationEnabled;
		return $this;
	}

	public function getNewFollowerEmailNotificationEnabled() {
		return $this->newFollowerEmailNotificationEnabled;
	}

	// NewMentionEmailNotificationEnabled /////

	public function setNewMentionEmailNotificationEnabled($newMentionEmailNotificationEnabled) {
		$this->newMentionEmailNotificationEnabled = $newMentionEmailNotificationEnabled;
		return $this;
	}

	public function getNewMentionEmailNotificationEnabled() {
		return $this->newMentionEmailNotificationEnabled;
	}

	// NewLikeEmailNotificationEnabled /////

	public function setNewLikeEmailNotificationEnabled($newLikeEmailNotificationEnabled) {
		$this->newLikeEmailNotificationEnabled = $newLikeEmailNotificationEnabled;
		return $this;
	}

	public function getNewLikeEmailNotificationEnabled() {
		return $this->newLikeEmailNotificationEnabled;
	}

	// NewVoteEmailNotificationEnabled /////

	public function setNewVoteEmailNotificationEnabled($newVoteEmailNotificationEnabled) {
		$this->newVoteEmailNotificationEnabled = $newVoteEmailNotificationEnabled;
		return $this;
	}

	public function getNewVoteEmailNotificationEnabled() {
		return $this->newVoteEmailNotificationEnabled;
	}

	// NewFollowingPostEmailNotificationEnabled /////

	public function setNewFollowingPostEmailNotificationEnabled($newPostEmailNotificationEnabled) {
		$this->newFollowingPostEmailNotificationEnabled = $newPostEmailNotificationEnabled;
		return $this;
	}

	public function getNewFollowingPostEmailNotificationEnabled() {
		return $this->newFollowingPostEmailNotificationEnabled;
	}

	// NewWatchActivityEmailNotificationEnabled /////

	public function setNewWatchActivityEmailNotificationEnabled($newCommentEmailNotificationEnabled) {
		$this->newWatchActivityEmailNotificationEnabled = $newCommentEmailNotificationEnabled;
		return $this;
	}

	public function getNewWatchActivityEmailNotificationEnabled() {
		return $this->newWatchActivityEmailNotificationEnabled;
	}

	// NewSpotlightEmailNotificationEnabled /////

	public function setNewSpotlightEmailNotificationEnabled($newSpotlightEmailNotificationEnabled) {
		$this->newSpotlightEmailNotificationEnabled = $newSpotlightEmailNotificationEnabled;
		return $this;
	}

	public function getNewSpotlightEmailNotificationEnabled() {
		return $this->newSpotlightEmailNotificationEnabled;
	}

	// WeekNewsEmailNotificationEnabled /////

	public function setWeekNewsEmailEnabled($weekNewsEmailEnabled) {
		$this->weekNewsEmailEnabled = $weekNewsEmailEnabled;
		return $this;
	}

	public function getWeekNewsEmailEnabled() {
		return $this->weekNewsEmailEnabled;
	}


	// UnlistedWonderCreationCount /////

	public function getUnlistedWonderCreationCount() {
		return $this->unlistedWonderCreationCount;
	}

	public function setUnlistedWonderCreationCount($unlistedWonderCreationCount) {
		$this->unlistedWonderCreationCount = $unlistedWonderCreationCount;
		return $this;
	}

	// UnlistedWonderPlanCount /////

	public function getUnlistedWonderPlanCount() {
		return $this->unlistedWonderPlanCount;
	}

	public function setUnlistedWonderPlanCount($unlistedWonderPlanCount) {
		$this->unlistedWonderPlanCount = $unlistedWonderPlanCount;
		return $this;
	}

	// UnlistedWonderWorkshopCount /////

	public function getUnlistedWonderWorkshopCount() {
		return $this->unlistedWonderWorkshopCount;
	}

	public function setUnlistedWonderWorkshopCount($unlistedWonderWorkshopCount) {
		$this->unlistedWonderWorkshopCount = $unlistedWonderWorkshopCount;
		return $this;
	}

	// UnlistedFindFindCount /////

	public function getUnlistedFindFindCount() {
		return $this->unlistedFindFindCount;
	}

	public function setUnlistedFindFindCount($unlistedFindFindCount) {
		$this->unlistedFindFindCount = $unlistedFindFindCount;
		return $this;
	}

	// UnlistedEventEventCount /////

	public function getUnlistedEventEventCount() {
		return $this->unlistedEventEventCount;
	}

	public function setUnlistedEventEventCount($unlistedEventEventCount) {
		$this->unlistedEventEventCount = $unlistedEventEventCount;
		return $this;
	}

	// UnlistedHowtoHowtoCount /////

	public function getUnlistedHowtoHowtoCount() {
		return $this->unlistedHowtoHowtoCount;
	}

	public function setUnlistedHowtoHowtoCount($unlistedHowtoHowtoCount) {
		$this->unlistedHowtoHowtoCount = $unlistedHowtoHowtoCount;
		return $this;
	}

	// UnlistedKnowledgeWoodCount /////

	public function getUnlistedKnowledgeWoodCount() {
		return $this->unlistedKnowledgeWoodCount;
	}

	public function setUnlistedKnowledgeWoodCount($unlistedKnowledgeWoodCount) {
		$this->unlistedKnowledgeWoodCount = $unlistedKnowledgeWoodCount;
		return $this;
	}

	// UnlistedKnowledgeProviderCount /////

	public function getUnlistedKnowledgeProviderCount() {
		return $this->unlistedKnowledgeProviderCount;
	}

	public function setUnlistedKnowledgeProviderCount($unlistedKnowledgeProviderCount) {
		$this->unlistedKnowledgeProviderCount = $unlistedKnowledgeProviderCount;
		return $this;
	}

	// UnlistedKnowledgeSchoolCount /////

	public function getUnlistedKnowledgeSchoolCount() {
		return $this->unlistedKnowledgeSchoolCount;
	}

	public function setUnlistedKnowledgeSchoolCount($unlistedKnowledgeSchoolCount) {
		$this->unlistedKnowledgeSchoolCount = $unlistedKnowledgeSchoolCount;
		return $this;
	}

	// UnlistedKnowledgeBookCount /////

	public function getUnlistedKnowledgeBookCount() {
		return $this->unlistedKnowledgeBookCount;
	}

	public function setUnlistedKnowledgeBookCount($unlistedKnowledgeBookCount) {
		$this->unlistedKnowledgeBookCount = $unlistedKnowledgeBookCount;
		return $this;
	}

	// UnlistedKnowledgeSoftwareCount /////

	public function getUnlistedKnowledgeSoftwareCount() {
		return $this->unlistedKnowledgeSoftwareCount;
	}

	public function setUnlistedKnowledgeSoftwareCount($unlistedKnowledgeSoftwareCount) {
		$this->unlistedKnowledgeSoftwareCount = $unlistedKnowledgeSoftwareCount;
		return $this;
	}

	// UnlistedKnowledgeToolCount /////

	public function getUnlistedKnowledgeToolCount() {
		return $this->unlistedKnowledgeToolCount;
	}

	public function setUnlistedKnowledgeToolCount($unlistedKnowledgeToolCount) {
		$this->unlistedKnowledgeToolCount = $unlistedKnowledgeToolCount;
		return $this;
	}

	// UnlistedBlogPostCount /////

	public function getUnlistedBlogPostCount() {
		return $this->unlistedBlogPostCount;
	}

	public function setUnlistedBlogPostCount($unlistedBlogPostCount) {
		$this->unlistedBlogPostCount = $unlistedBlogPostCount;
		return $this;
	}

	// UnlistedFaqQuestionCount /////

	public function getUnlistedFaqQuestionCount() {
		return $this->unlistedFaqQuestionCount;
	}

	public function setUnlistedFaqQuestionCount($unlistedFaqQuestionCount) {
		$this->unlistedFaqQuestionCount = $unlistedFaqQuestionCount;
		return $this;
	}

	// UnlistedQaQuestionCount /////

	public function getUnlistedQaQuestionCount() {
		return $this->unlistedQaQuestionCount;
	}

	public function setUnlistedQaQuestionCount($unlistedQaQuestionCount) {
		$this->unlistedQaQuestionCount = $unlistedQaQuestionCount;
		return $this;
	}

	// UnlistedPromotionGraphicCount /////

	public function getUnlistedPromotionGraphicCount() {
		return $this->unlistedPromotionGraphicCount;
	}

	public function setUnlistedPromotionGraphicCount($unlistedPromotionGraphicCount) {
		$this->unlistedPromotionGraphicCount = $unlistedPromotionGraphicCount;
		return $this;
	}

	// UnlistedWorkflowWorkflowCount /////

	public function getUnlistedWorkflowWorkflowCount() {
		return $this->unlistedWorkflowWorkflowCount;
	}

	public function setUnlistedWorkflowWorkflowCount($unlistedWorkflowWorkflowCount) {
		$this->unlistedWorkflowWorkflowCount = $unlistedWorkflowWorkflowCount;
		return $this;
	}

	// UnlistedCollectionCollectionCount /////

	public function getUnlistedCollectionCollectionCount() {
		return $this->unlistedCollectionCollectionCount;
	}

	public function setUnlistedCollectionCollectionCount($unlistedCollectionCollectionCount) {
		$this->unlistedCollectionCollectionCount = $unlistedCollectionCollectionCount;
		return $this;
	}

	// UnlistedOfferOfferCount /////

	public function getUnlistedOfferOfferCount() {
		return $this->unlistedOfferOfferCount;
	}

	public function setUnlistedOfferOfferCount($unlistedOfferOfferCount) {
		$this->unlistedOfferOfferCount = $unlistedOfferOfferCount;
		return $this;
	}


	// MemberCount /////

	public function incrementMemberCount($by = 1) {
		return $this->memberCount += intval($by);
	}

	public function getMemberCount() {
		return $this->memberCount;
	}

	// InvitationCount /////

	public function incrementInvitationCount($by = 1) {
		return $this->invitationCount += intval($by);
	}

	public function getInvitationCount() {
		return $this->invitationCount;
	}

	// RequestCount /////

	public function incrementRequestCount($by = 1) {
		return $this->requestCount += intval($by);
	}

	public function getRequestCount() {
		return $this->requestCount;
	}

	// TeamCount /////

	public function incrementTeamCount($by = 1) {
		return $this->teamCount += intval($by);
	}

	public function getTeamCount() {
		return $this->teamCount;
	}


	// FollowerCount /////

	public function incrementFollowerCount($by = 1) {
		return $this->followerCount += intval($by);
	}

	public function getFollowerCount() {
		return $this->followerCount;
	}

	// FollowingCount /////

	public function incrementFollowingCount($by = 1) {
		return $this->followingCount += intval($by);
	}

	public function getFollowingCount() {
		return $this->followingCount;
	}


	// RecievedLikeCount /////

	public function incrementRecievedLikeCount($by = 1) {
		return $this->recievedLikeCount += intval($by);
	}

	public function getRecievedLikeCount() {
		return $this->recievedLikeCount;
	}

	// SentLikeCount /////

	public function incrementSentLikeCount($by = 1) {
		return $this->sentLikeCount += intval($by);
	}

	public function getSentLikeCount() {
		return $this->sentLikeCount;
	}


	// PositiveVoteCount /////

	public function incrementPositiveVoteCount($by = 1) {
		return $this->positiveVoteCount += intval($by);
	}

	public function getPositiveVoteCount() {
		return $this->positiveVoteCount;
	}

	// NegativeVoteCount /////

	public function incrementNegativeVoteCount($by = 1) {
		return $this->negativeVoteCount += intval($by);
	}

	public function getNegativeVoteCount() {
		return $this->negativeVoteCount;
	}


	// UnreadMessageCount /////

	public function setUnreadMessageCount($unreadMessageCount) {
		$this->unreadMessageCount = $unreadMessageCount;
		return $this;
	}

	public function incrementUnreadMessageCount($by = 1) {
		return $this->unreadMessageCount = max(0, $this->unreadMessageCount + intval($by));
	}

	public function getUnreadMessageCount() {
		return $this->unreadMessageCount;
	}

	// FreshNotificationCount /////

	public function setFreshNotificationCount($freshNotificationCount) {
		$this->freshNotificationCount = $freshNotificationCount;
		return $this;
	}

	public function incrementFreshNotificationCount($by = 1) {
		return $this->freshNotificationCount += intval($by);
	}

	public function getFreshNotificationCount() {
		return $this->freshNotificationCount;
	}

	// NotificationsFoldingSince /////

	public function setNotificationsFoldingSince($notificationsFoldingSince) {
		$this->notificationsFoldingSince = $notificationsFoldingSince;
		return $this;
	}

	public function getNotificationsFoldingSince() {
		return $this->notificationsFoldingSince;
	}


	// CommentCount /////

	public function setCommentCount($commentCount) {
		$this->commentCount = $commentCount;
		return $this;
	}

	public function incrementCommentCount($by = 1) {
		$this->incrementContributionCount($by);
		return $this->commentCount += intval($by);
	}

	public function getCommentCount() {
		return $this->commentCount;
	}


	// ContributionCount /////

	public function setContributionCount($contributionCount) {
		$this->contributionCount = $contributionCount;
		return $this;
	}

	public function incrementContributionCount($by = 1) {
		return $this->contributionCount += intval($by);
	}

	public function getContributionCount() {
		return $this->contributionCount;
	}

	// PrivateCreationCount /////

	public function setPrivateCreationCount($privateCreationCount) {
		$this->privateCreationCount = $privateCreationCount;
		return $this;
	}

	public function incrementPrivateCreationCount($by = 1) {
		return $this->privateCreationCount += intval($by);
	}

	public function getPrivateCreationCount() {
		return $this->privateCreationCount;
	}

	// PublicCreationCount /////

	public function setPublicCreationCount($publicCreationCount) {
		$this->publicCreationCount = $publicCreationCount;
		return $this;
	}

	public function incrementPublicCreationCount($by = 1) {
		$this->incrementContributionCount($by);
		return $this->publicCreationCount += intval($by);
	}

	public function getPublicCreationCount() {
		return $this->publicCreationCount;
	}

	// PrivatePlanCount /////

	public function setPrivatePlanCount($privatePlanCount) {
		$this->privatePlanCount = $privatePlanCount;
		return $this;
	}

	public function incrementPrivatePlanCount($by = 1) {
		return $this->privatePlanCount += intval($by);
	}

	public function getPrivatePlanCount() {
		return $this->privatePlanCount;
	}

	// PublicPlanCount /////

	public function setPublicPlanCount($publicPlanCount) {
		$this->publicPlanCount = $publicPlanCount;
		return $this;
	}

	public function incrementPublicPlanCount($by = 1) {
		$this->incrementContributionCount($by);
		return $this->publicPlanCount += intval($by);
	}

	public function getPublicPlanCount() {
		return $this->publicPlanCount;
	}

	// PrivateHowtoCount /////

	public function setPrivateHowtoCount($privateHowtoCount) {
		$this->privateHowtoCount = $privateHowtoCount;
		return $this;
	}

	public function incrementPrivateHowtoCount($by = 1) {
		return $this->privateHowtoCount += intval($by);
	}

	public function getPrivateHowtoCount() {
		return $this->privateHowtoCount;
	}

	// PublicHowtoCount /////

	public function setPublicHowtoCount($publicHowtoCount) {
		$this->publicHowtoCount = $publicHowtoCount;
		return $this;
	}

	public function incrementPublicHowtoCount($by = 1) {
		$this->incrementContributionCount($by);
		return $this->publicHowtoCount += intval($by);
	}

	public function getPublicHowtoCount() {
		return $this->publicHowtoCount;
	}

	// PrivateWorkshopCount /////

	public function setPrivateWorkshopCount($privateWorkshopCount) {
		$this->privateWorkshopCount = $privateWorkshopCount;
		return $this;
	}

	public function incrementPrivateWorkshopCount($by = 1) {
		return $this->privateWorkshopCount += intval($by);
	}

	public function getPrivateWorkshopCount() {
		return $this->privateWorkshopCount;
	}

	// PublicWorkshopCount /////

	public function setPublicWorkshopCount($publicWorkshopCount) {
		$this->publicWorkshopCount = $publicWorkshopCount;
		return $this;
	}

	public function incrementPublicWorkshopCount($by = 1) {
		$this->incrementContributionCount($by);
		return $this->publicWorkshopCount += intval($by);
	}

	public function getPublicWorkshopCount() {
		return $this->publicWorkshopCount;
	}

	// PrivateFindCount /////

	public function setPrivateFindCount($privateFindCount) {
		$this->privateFindCount = $privateFindCount;
		return $this;
	}

	public function incrementPrivateFindCount($by = 1) {
		return $this->privateFindCount += intval($by);
	}

	public function getPrivateFindCount() {
		return $this->privateFindCount;
	}

	// PublicFindCount /////

	public function setPublicFindCount($publicFindCount) {
		$this->publicFindCount = $publicFindCount;
		return $this;
	}

	public function incrementPublicFindCount($by = 1) {
		$this->incrementContributionCount($by);
		return $this->publicFindCount += intval($by);
	}

	public function getPublicFindCount() {
		return $this->publicFindCount;
	}

	// PrivateEventCount /////

	public function setPrivateEventCount($privateEventCount) {
		$this->privateEventCount = $privateEventCount;
		return $this;
	}

	public function incrementPrivateEventCount($by = 1) {
		return $this->privateEventCount += intval($by);
	}

	public function getPrivateEventCount() {
		return $this->privateEventCount;
	}

	// PublicEventCount /////

	public function setPublicEventCount($publicEventCount) {
		$this->publicEventCount = $publicEventCount;
		return $this;
	}

	public function incrementPublicEventCount($by = 1) {
		$this->incrementContributionCount($by);
		return $this->publicEventCount += intval($by);
	}

	public function getPublicEventCount() {
		return $this->publicEventCount;
	}

	// PrivateQuestionCount /////

	public function setPrivateQuestionCount($privateQuestionCount) {
		$this->privateQuestionCount = $privateQuestionCount;
		return $this;
	}

	public function incrementPrivateQuestionCount($by = 1) {
		return $this->privateQuestionCount += intval($by);
	}

	public function getPrivateQuestionCount() {
		return $this->privateQuestionCount;
	}

	// PublicQuestionCount /////

	public function setPublicQuestionCount($publicQuestionCount) {
		$this->publicQuestionCount = $publicQuestionCount;
		return $this;
	}

	public function incrementPublicQuestionCount($by = 1) {
		$this->incrementContributionCount($by);
		return $this->publicQuestionCount += intval($by);
	}

	public function getPublicQuestionCount() {
		return $this->publicQuestionCount;
	}

	// AnswerCount /////

	public function setAnswerCount($answerCount) {
		$this->answerCount = $answerCount;
		return $this;
	}

	public function incrementAnswerCount($by = 1) {
		$this->incrementContributionCount($by);
		return $this->answerCount += intval($by);
	}

	public function getAnswerCount() {
		return $this->answerCount;
	}

	// PrivateOfferCount /////

	public function setPrivateOfferCount($privateOfferCount) {
		$this->privateOfferCount = $privateOfferCount;
		return $this;
	}

	public function incrementPrivateOfferCount($by = 1) {
		return $this->privateOfferCount += intval($by);
	}

	public function getPrivateOfferCount() {
		return $this->privateOfferCount;
	}

	// PublicOfferCount /////

	public function setPublicOfferCount($publicOfferCount) {
		$this->publicOfferCount = $publicOfferCount;
		return $this;
	}

	public function incrementPublicOfferCount($by = 1) {
		$this->incrementContributionCount($by);
		return $this->publicOfferCount += intval($by);
	}

	public function getPublicOfferCount() {
		return $this->publicOfferCount;
	}

	// PrivateGraphicCount /////

	public function setPrivateGraphicCount($privateGraphicCount) {
		$this->privateGraphicCount = $privateGraphicCount;
		return $this;
	}

	public function incrementPrivateGraphicCount($by = 1) {
		return $this->privateGraphicCount += intval($by);
	}

	public function getPrivateGraphicCount() {
		return $this->privateGraphicCount;
	}

	// PublicGraphicCount /////

	public function setPublicGraphicCount($publicGraphicCount) {
		$this->publicGraphicCount = $publicGraphicCount;
		return $this;
	}

	public function incrementPublicGraphicCount($by = 1) {
		$this->incrementContributionCount($by);
		return $this->publicGraphicCount += intval($by);
	}

	public function getPublicGraphicCount() {
		return $this->publicGraphicCount;
	}

	// PrivateWorkflowCount /////

	public function setPrivateWorkflowCount($privateWorkflowCount) {
		$this->privateWorkflowCount = $privateWorkflowCount;
		return $this;
	}

	public function incrementPrivateWorkflowCount($by = 1) {
		return $this->privateWorkflowCount += intval($by);
	}

	public function getPrivateWorkflowCount() {
		return $this->privateWorkflowCount;
	}

	// PublicWorkflowCount /////

	public function setPublicWorkflowCount($publicWorkflowCount) {
		$this->publicWorkflowCount = $publicWorkflowCount;
		return $this;
	}

	public function incrementPublicWorkflowCount($by = 1) {
		$this->incrementContributionCount($by);
		return $this->publicWorkflowCount += intval($by);
	}

	public function getPublicWorkflowCount() {
		return $this->publicWorkflowCount;
	}

	// PrivateCollectionCount /////

	public function setPrivateCollectionCount($privateCollectionCount) {
		$this->privateCollectionCount = $privateCollectionCount;
		return $this;
	}

	public function incrementPrivateCollectionCount($by = 1) {
		return $this->privateCollectionCount += intval($by);
	}

	public function getPrivateCollectionCount() {
		return $this->privateCollectionCount;
	}

	// PublicCollectionCount /////

	public function setPublicCollectionCount($publicCollectionCount) {
		$this->publicCollectionCount = $publicCollectionCount;
		return $this;
	}

	public function incrementPublicCollectionCount($by = 1) {
		return $this->publicCollectionCount += intval($by);
	}

	public function getPublicCollectionCount() {
		return $this->publicCollectionCount;
	}

	// ProposalCount /////

	public function setProposalCount($proposalCount) {
		$this->proposalCount = $proposalCount;
		return $this;
	}

	public function incrementProposalCount($by = 1) {
		$this->incrementContributionCount($by);
		return $this->proposalCount += intval($by);
	}

	public function getProposalCount() {
		return $this->proposalCount;
	}

	// TestimonialCount /////

	public function setTestimonialCount($testimonialCount) {
		$this->testimonialCount = $testimonialCount;
		return $this;
	}

	public function incrementTestimonialCount($by = 1) {
		$this->incrementContributionCount($by);
		return $this->testimonialCount += intval($by);
	}

	public function getTestimonialCount() {
		return $this->testimonialCount;
	}

	// ReviewCount /////

	public function setReviewCount($reviewCount) {
		$this->reviewCount = $reviewCount;
		return $this;
	}

	public function incrementReviewCount($by = 1) {
		$this->incrementContributionCount($by);
		return $this->reviewCount += intval($by);
	}

	public function getReviewCount() {
		return $this->reviewCount;
	}


	// FeedbackCount /////

	public function setFeedbackCount($feedbackCount) {
		$this->feedbackCount = $feedbackCount;
		return $this;
	}

	public function incrementFeedbackCount($by = 1) {
		$this->incrementContributionCount($by);
		return $this->feedbackCount += intval($by);
	}

	public function getFeedbackCount() {
		return $this->feedbackCount;
	}


	// DonationCount /////

	public function incrementDonationCount($by = 1) {
		return $this->donationCount += intval($by);
	}

	public function getDonationCount() {
		return $this->donationCount;
	}

	// DonationBalance /////

	public function incrementDonationBalance($by = 1) {
		return $this->donationBalance += intval($by);
	}

	public function getDonationBalanceEur() {
		return $this->getDonationBalance() / 100;
	}

	public function getDonationBalance() {
		return $this->donationBalance;
	}

	// DonationFeeBalance /////

	public function incrementDonationFeeBalance($by = 1) {
		return $this->donationFeeBalance += intval($by);
	}

	public function getDonationFeeBalanceEur() {
		return $this->getDonationFeeBalance() / 100;
	}

	public function getDonationFeeBalance() {
		return $this->donationFeeBalance;
	}

}
