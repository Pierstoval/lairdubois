<?php

namespace App\Controller\Core;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use App\Controller\AbstractController;
use App\Entity\Core\Comment;
use App\Form\Model\NewVote;
use App\Form\Type\Core\NewVoteType;
use App\Model\AuthoredInterface;
use App\Utils\CommentableUtils;
use App\Model\VotableInterface;
use App\Model\VotableParentInterface;
use App\Utils\VotableUtils;
use App\Utils\PaginatorUtils;
use App\Utils\ActivityUtils;
use App\Utils\TypableUtils;
use App\Entity\Core\Vote;
use App\Event\VotableEvent;
use App\Event\VotableListener;

/**
 * @Route("/votes")
 */
class VoteController extends AbstractController {

	private function _retriveRelatedEntityRepository($entityType) {

		$typableUtils = $this->get(TypableUtils::class);
		try {
			$entityRepository = $typableUtils->getRepositoryByType($entityType);
		} catch (\Exception $e) {
			throw $this->createNotFoundException($e->getMessage());
		}

		return $entityRepository;
	}

	private function _retriveRelatedEntity($entityRepository, $entityId) {

		$entity = $entityRepository->findOneById($entityId);
		if (is_null($entity)) {
			throw $this->createNotFoundException('Unknow Entity Id (entityId='.$entityId.').');
		}
		if (!($entity instanceof VotableInterface)) {
			throw $this->createNotFoundException('Entity must implements VotableInterface.');
		}
		if ($entity instanceof AuthoredInterface && $entity->getUser() == $this->getUser()) {
			throw $this->createNotFoundException('Not allowed (vote->_retriveRelatedEntity)');
		}

		return $entity;
	}

	private function _retrieveRelatedParentEntityRepository($entity) {

		$typableUtils = $this->get(TypableUtils::class);
		try {
			$parentEntityRepository = $typableUtils->getRepositoryByType($entity->getParentEntityType());
		} catch (\Exception $e) {
			throw $this->createNotFoundException('Unknow Parent Entity Type (groupEntityType='.$entity->getParentEntityType().').');
		}

		return $parentEntityRepository;
	}

	private function _retrieveRelatedParentEntity($parentEntityRepository, $entity) {

		$parentEntity = $parentEntityRepository->findOneById($entity->getParentEntityId());
		if (is_null($parentEntity)) {
			throw $this->createNotFoundException('Unknow Parent Entity Id (entityId='.$entity->getParentEntityId().').');
		}
		if (!($parentEntity instanceof VotableParentInterface)) {
			throw $this->createNotFoundException('Parent Entity must implements VotableParentInterface.');
		}

		return $parentEntity;
	}

	/////

	/**
	 * @Route("/{entityType}/{entityId}/{way}/new", requirements={"entityType" = "\d+", "entityId" = "\d+", "way" = "up|down"}, name="core_vote_new")
	 * @Template("Core/Vote/new-xhr.html.twig")
	 */
	public function new(Request $request, $entityType, $entityId, $way) {
		if (!$request->isXmlHttpRequest()) {
			throw $this->createNotFoundException('Only XML request allowed (core_vote_down_new)');
		}

		// Exclude vote if user is not email confirmed
		if (!$this->getUser()->getEmailConfirmed()) {
			throw $this->createNotFoundException('Not allowed - User email not confirmed (core_vote_create)');
		}

		// Retrieve related entity

		$entityRepository = $this->_retriveRelatedEntityRepository($entityType);
		$entity = $this->_retriveRelatedEntity($entityRepository, $entityId);

		// Retrieve related parent entity

		$parentEntityRepository = $this->_retrieveRelatedParentEntityRepository($entity);
		$parentEntity = $this->_retrieveRelatedParentEntity($parentEntityRepository, $entity);

		// Get orientation parameter
		$orientation = $request->get('orientation', 'auto');

		$newVote = new NewVote();
		$form = $this->createForm(NewVoteType::class, $newVote);

		return array(
			'orientation'  => $orientation,
			'entity'       => $entity,
			'parentEntity' => $parentEntity,
			'way'          => $way,
			'form'         => $form->createView(),
		);
	}

	/**
	 * @Route("/{entityType}/{entityId}/{way}/create", requirements={"entityType" = "\d+", "entityId" = "\d+", "way" = "up|down"}, name="core_vote_create")
	 */
	public function create(Request $request, $entityType, $entityId, $way) {
		if (!$request->isXmlHttpRequest()) {
			throw $this->createNotFoundException('Only XML request allowed (core_vote_create)');
		}

		$this->createLock('core_vote_create', false, self::LOCK_TTL_CREATE_ACTION, false);

		// Exclude vote if user is not email confirmed
		if (!$this->getUser()->getEmailConfirmed()) {
			throw $this->createNotFoundException('Not allowed - User email not confirmed (core_vote_create)');
		}

		// Retrieve related entity

		$entityRepository = $this->_retriveRelatedEntityRepository($entityType);
		$entity = $this->_retriveRelatedEntity($entityRepository, $entityId);

		// Retrieve related parent entity

		$parentEntityRepository = $this->_retrieveRelatedParentEntityRepository($entity);
		$parentEntity = $this->_retrieveRelatedParentEntity($parentEntityRepository, $entity);

		// Get orientation parameter
		$orientation = $request->get('orientation', 'auto');

		// Compute score
		$score = $way == 'down' ? -1 : 1;

		// Declare form validation function
		$validateFormFn = function() use ($request, $orientation, $way, $entity, $parentEntity) {

			// Check form if it exists
			if ($request->isMethod('post')) {

				$newVote = new NewVote();
				$form = $this->createForm(NewVoteType::class, $newVote, array(
					'validation_groups' => array( $way )
				));
				$form->handleRequest($request);

				if (!$form->isValid()) {

					return $this->render('Core/Vote/new-xhr.html.twig', array(
						'orientation'  => $orientation,
						'entity'       => $entity,
						'parentEntity' => $parentEntity,
						'way'          => $way,
						'form'         => $form->createView(),
					));

				}

				// Check if new vote contains reason
				if (!empty($newVote->getBody())) {

					$comment = new Comment();
					$comment->setBody($newVote->getBody());
					$comment->setEntityType($entity->getType());
					$comment->setEntityId($entity->getId());
					$comment->setUser($this->getUser());

					$commentableUtils = $this->get(CommentableUtils::class);
					$commentableUtils->finalizeNewComment($comment, $entity);

					return $comment;
				}

			} else {
				throw $this->createNotFoundException('Only POST allowed (core_vote_create)');
			}

			return null;
		};

		// Process vote

		$om = $this->getDoctrine()->getManager();
		$voteRepository = $om->getRepository(Vote::CLASS_NAME);

		$vote = $voteRepository->findOneByEntityTypeAndEntityIdAndUser($entity->getType(), $entity->getId(), $this->getUser());
		if (is_null($vote)) {

			// Check form if it exists
			$comment = $validateFormFn();
			if ($comment instanceof Response) {
				return $comment;
			};

			// Create a new vote
			$vote = new Vote();
			$vote->setEntityType($entityType);
			$vote->setEntityId($entityId);
			$vote->setParentEntityType($parentEntity->getType());
			$vote->setParentEntityId($parentEntity->getId());
			$vote->setParentEntityField($entity->getParentEntityField());
			$vote->setUser($this->getUser());
			$vote->setScore($score);

			// Link vote to comment
			$vote->setComment($comment);
			if (!is_null($comment)) {
				$comment->setVote($vote);
			}

			$om->persist($vote);

			// Update related entity
			$entity->incrementVoteScore($score);
			$entity->incrementVoteCount();
			$parentEntity->incrementVoteCount();
			if ($score > 0) {
				$entity->incrementPositiveVoteScore($score);
				$parentEntity->incrementPositiveVoteCount();
				$this->getUser()->getMeta()->incrementPositiveVoteCount();
			} else {
				$entity->incrementNegativeVoteScore(abs($score));
				$parentEntity->incrementNegativeVoteCount();
				$this->getUser()->getMeta()->incrementNegativeVoteCount();
			}

		} else {

			if ($score != $vote->getScore()) {

				// Check form if it exists
				$comment = $validateFormFn();
				if ($comment instanceof Response) {
					return $comment;
				};

				// Update related entity
				$entity->incrementVoteScore(-$vote->getScore() + $score);
				if ($vote->getScore() > 0) {
					$entity->incrementPositiveVoteScore(-$vote->getScore());
					$parentEntity->incrementPositiveVoteCount(-1);
					$this->getUser()->getMeta()->incrementPositiveVoteCount(-1);
				} else {
					$entity->incrementNegativeVoteScore(-abs($vote->getScore()));
					$parentEntity->incrementNegativeVoteCount(-1);
					$this->getUser()->getMeta()->incrementNegativeVoteCount(-1);
				}
				if ($score > 0) {
					$entity->incrementPositiveVoteScore($score);
					$parentEntity->incrementPositiveVoteCount();
					$this->getUser()->getMeta()->incrementPositiveVoteCount();
				} else {
					$entity->incrementNegativeVoteScore(abs($score));
					$parentEntity->incrementNegativeVoteCount();
					$this->getUser()->getMeta()->incrementNegativeVoteCount();
				}

				// Update vote
				$vote->setScore($score);

				// Delete previous comment link
				if (!is_null($vote->getComment())) {
					$vote->getComment()->setVote(null);
				}

				// Link vote to comment
				$vote->setComment($comment);
				if (!is_null($comment)) {
					$comment->setVote($vote);
				}

				// Delete activities
				$activityUtils = $this->get(ActivityUtils::class);
				$activityUtils->deleteActivitiesByVote($vote, false);

			} else {
				throw $this->createNotFoundException('Can\'t vote twice for the same Votable (core_vote_create)');
			}

		}

		// Dispatch votable parent event
		$dispatcher = $this->get('event_dispatcher');
		$dispatcher->dispatch(new VotableEvent($entity, $parentEntity), VotableListener::VOTE_UPDATED);

		// Create activity
		$activityUtils = $this->get(ActivityUtils::class);
		$activityUtils->createVoteActivity($vote, false);

		$om->flush();

		if ($request->isXmlHttpRequest()) {

			$votableUtils = $this->get(VotableUtils::class);

			return $this->render('Core/Vote/create-xhr.html.twig', array(
				'orientation' => $orientation,
				'voteContext' => $votableUtils->getVoteContext($entity, $this->getUser()),
			));
		}

		// Return to

		$returnToUrl = $request->get('rtu');
		if (is_null($returnToUrl)) {
			$returnToUrl = $request->headers->get('referer');
		}

		return $this->redirect($returnToUrl);
	}

	/**
	 * @Route("/{id}/delete", requirements={"id" = "\d+"}, name="core_vote_delete")
	 */
	public function delete(Request $request, $id) {
		if (!$request->isXmlHttpRequest()) {
			throw $this->createNotFoundException('Only XML request allowed (core_vote_delete)');
		}

		// Get orientation parameter
		$orientation = $request->get('orientation', 'auto');

		$om = $this->getDoctrine()->getManager();
		$voteRepository = $om->getRepository(Vote::CLASS_NAME);

		$vote = $voteRepository->findOneById($id);
		if (is_null($vote)) {
			throw $this->createNotFoundException('Unable to find Vote entity (id='.$id.').');
		}
		if ($vote->getUser()->getId() != $this->getUser()->getId()) {
			throw $this->createNotFoundException('Not allowed (core_vote_delete)');
		}

		// Unlink comment
		if (!is_null($vote->getComment())) {
			$vote->getComment()->setVote(null);
		}

		$om->remove($vote);

		// Retrieve related entity

		$entityRepository = $this->_retriveRelatedEntityRepository($vote->getEntityType());
		$entity = $this->_retriveRelatedEntity($entityRepository, $vote->getEntityId());

		// Retrieve related parent entity

		$parentEntityRepository = $this->_retrieveRelatedParentEntityRepository($entity);
		$parentEntity = $this->_retrieveRelatedParentEntity($parentEntityRepository, $entity);

		// Update related entity

		if ($vote->getScore() > 0) {
			$entity->incrementPositiveVoteScore(-$vote->getScore());
			$parentEntity->incrementPositiveVoteCount(-1);
			$this->getUser()->getMeta()->incrementPositiveVoteCount(-1);
		} else {
			$entity->incrementNegativeVoteScore(-abs($vote->getScore()));
			$parentEntity->incrementNegativeVoteCount(-1);
			$this->getUser()->getMeta()->incrementNegativeVoteCount(-1);
		}
		$entity->incrementVoteScore(-$vote->getScore());
		$entity->incrementVoteCount(-1);
		$parentEntity->incrementVoteCount(-1);

		// Delete activities
		$activityUtils = $this->get(ActivityUtils::class);
		$activityUtils->deleteActivitiesByVote($vote, false);

		// Dispatch votable parent event
		$dispatcher = $this->get('event_dispatcher');
		$dispatcher->dispatch(new VotableEvent($entity, $parentEntity), VotableListener::VOTE_UPDATED);

		$om->flush();

		if ($request->isXmlHttpRequest()) {

			$votableUtils = $this->get(VotableUtils::class);

			return $this->render('Core/Vote/delete-xhr.html.twig', array(
				'orientation' => $orientation,
				'voteContext' => $votableUtils->getVoteContext($entity, $this->getUser()),
			));
		}

		// Return to (use referer because the user is already logged)

		$returnToUrl = $request->headers->get('referer');

		return $this->redirect($returnToUrl);
	}

	/**
	 * @Route("/p/{entityType}/{entityId}", requirements={"entityType" = "\d+", "entityId" = "\d+"}, name="core_vote_list_parent_entity")
	 * @Route("/p/{entityType}/{entityId}/{filter}", requirements={"entityType" = "\d+", "entityId" = "\d+", "filter" = "positive|negative"}, name="core_vote_list_parent_entity_filter")
	 * @Route("/p/{entityType}/{entityId}/{filter}/{page}", requirements={"entityType" = "\d+", "entityId" = "\d+", "filter" = "positive|negative", "page" = "\d+"}, name="core_vote_list_parent_entity_filter_page")
	 * @Template("Core/Vote/list-byparent.html.twig")
	 */
	public function showParentVotes(Request $request, $entityType, $entityId, $filter = 'positive', $page = 0) {

		// Retrieve related parent entity

		$entityRepository = $this->_retriveRelatedEntityRepository($entityType);
		$entity = $entityRepository->findOneById($entityId);
		if (is_null($entity)) {
			throw $this->createNotFoundException('Unknow Parent Entity Id (entityId='.$entityId.').');
		}
		if (!($entity instanceof VotableParentInterface)) {
			throw $this->createNotFoundException('Parent Entity must implements VotableParentInterface.');
		}

		$om = $this->getDoctrine()->getManager();
		$voteRepository = $om->getRepository(Vote::CLASS_NAME);
		$paginatorUtils = $this->get(PaginatorUtils::class);

		$offset = $paginatorUtils->computePaginatorOffset($page);
		$limit = $paginatorUtils->computePaginatorLimit($page);
		$items = $voteRepository->findPaginedByVotableParent($entity, $offset, $limit, $filter);
		$pageUrls = $paginatorUtils->generatePrevAndNextPageUrl('core_vote_list_parent_entity_filter_page', array( 'entityType' => $entityType, 'entityId' => $entityId, 'filter' => $filter ), $page, $entity->getVoteCount());

		$parameters = array(
			'filter'      => $filter,
			'prevPageUrl' => $pageUrls->prev,
			'nextPageUrl' => $pageUrls->next,
			'entity'      => $entity,
			'items'       => $items,
		);

		if ($request->isXmlHttpRequest()) {
			return $this->render('Core/Vote/list-byparent-xhr.html.twig', $parameters);
		}

		return $parameters;
	}

}