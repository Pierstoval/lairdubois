<?php

namespace App\Repository\Core;

use App\Entity\Core\User;
use App\Model\HiddableInterface;
use App\Model\ViewableInterface;
use App\Model\VotableInterface;
use App\Model\VotableParentInterface;
use App\Repository\AbstractEntityRepository;
use App\Utils\TypableUtils;

class VoteRepository extends AbstractEntityRepository {

	/////

	public function findOneByEntityTypeAndEntityIdAndUser($entityType, $entityId, User $user) {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'v' ))
			->from($this->getEntityName(), 'v')
			->where('v.entityType = :entityType')
			->andWhere('v.entityId = :entityId')
			->andWhere('v.user = :user')
			->setParameter('entityType', $entityType)
			->setParameter('entityId', $entityId)
			->setParameter('user', $user)
		;

		try {
			return $queryBuilder->getQuery()->getSingleResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}

	public function existsByEntityTypeAndEntityIdAndUser($entityType, $entityId, User $user) {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select('count(v.id)')
			->from($this->getEntityName(), 'v')
			->where('v.entityType = :entityType')
			->andWhere('v.entityId = :entityId')
			->andWhere('v.user = :user')
			->setParameter('entityType', $entityType)
			->setParameter('entityId', $entityId)
			->setParameter('user', $user)
		;

		try {
			return $queryBuilder->getQuery()->getSingleScalarResult() > 0;
		} catch (\Doctrine\ORM\NoResultException $e) {
			return false;
		}
	}

	/////

	public function findByEntityTypeAndEntityId($entityType, $entityId) {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'v' ))
			->from($this->getEntityName(), 'v')
			->where('v.entityType = :entityType')
			->andWhere('v.entityId = :entityId')
			->setParameter('entityType', $entityType)
			->setParameter('entityId', $entityId)
		;

		try {
			return $queryBuilder->getQuery()->getResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}

	/////

	/*
	 * [
	 * 	[ 'entity' => ENTITY, 'parentEntity' => PARENT_ENTITY, 'vote' => VOTE ],
	 *  ...,
	 * ]
	 */
	public function findPaginedByUserGroupByEntityType(User $user, $offset, $limit, $filter = 'positive') {

		// Retrieve concat vote ids per entity
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'v', 'MAX(v.createdAt) AS mx', 'v.entityType', 'v.entityId', 'count(v.id)' ))
			->from($this->getEntityName(), 'v')
			->where('v.user = :user')
			->groupBy('v.entityType, v.entityId')
			->orderBy('mx', 'DESC')
			->setParameter('user', $user)
			->setFirstResult($offset)
			->setMaxResults($limit)
		;
		if ($filter == 'positive') {
			$queryBuilder
				->andWhere('v.score > 0')
			;
		} else {
			$queryBuilder
				->andWhere('v.score < 0')
			;
		}
		try {
			$concatResults = $queryBuilder->getQuery()->getResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
		}

		$items = array();

		foreach ($concatResults as $concatResult) {

			$vote = $concatResult[0];
			$entityType = $concatResult['entityType'];
			$entityId = $concatResult['entityId'];

			// Retrive related entity
			$entityClassName = TypableUtils::getClassByType($entityType);
			if (is_null($entityClassName)) {
				continue;
			}
			$entityRepository = $this->getEntityManager()->getRepository($entityClassName);
			$entity = $entityRepository->findOneByIdJoinedOn($entityId, $entityRepository->getDefaultJoinOptions());
			if (is_null($entity)) {
				continue;
			}
			if ($entity instanceof HiddableInterface && !$entity->getIsPublic()) {
				continue;
			}
			if (!($entity instanceof VotableInterface)) {
				continue;
			}
			$parentEntityClassName = TypableUtils::getClassByType($entity->getParentEntityType());
			if (is_null($parentEntityClassName)) {
				continue;
			}
			$parentEntityRepository = $this->getEntityManager()->getRepository($parentEntityClassName);
			$parentEntity = $parentEntityRepository->findOneByIdJoinedOn($entity->getParentEntityId(), $parentEntityRepository->getDefaultJoinOptions());
			if (is_null($parentEntity)) {
				continue;
			}
			if ($entity instanceof HiddableInterface && !$entity->getIsPublic()) {
				continue;
			}

			$items[] = array(
				'entity'       => $entity,
				'parentEntity' => $parentEntity,
				'vote'         => $vote,
			);

		}

		return $items;
	}

	/*
	 * [
	 * 	[ 'user' => USER, 'votables' => VOTABLES ],
	 *  ...,
	 * ]
	 */
	public function findPaginedByVotableParent(VotableParentInterface $votableParent, $offset, $limit, $filter = 'positive') {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'v', 'u', 'MAX(v.id) AS mx', 'COUNT(v.id)', 'GROUP_CONCAT(v.id ORDER BY v.id ASC)', 'GROUP_CONCAT(v.entityType ORDER BY v.id ASC)', 'GROUP_CONCAT(v.entityId ORDER BY v.id ASC)' ))
			->from($this->getEntityName(), 'v')
			->innerJoin('v.user', 'u')
			->orderBy('mx', 'DESC')
			->where('v.parentEntityType = :parentEntityType')
			->andWhere('v.parentEntityId = :parentEntityId')
			->groupBy('v.user')
			->setParameter('parentEntityType', $votableParent->getType())
			->setParameter('parentEntityId', $votableParent->getId())
			->setFirstResult($offset)
			->setMaxResults($limit)
		;
		if ($filter == 'positive') {
			$queryBuilder
				->andWhere('v.score > 0')
			;
		} else {
			$queryBuilder
				->andWhere('v.score < 0')
			;
		}
		try {
			$concatResults = $queryBuilder->getQuery()->getResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
		}

		$items = array();

		foreach ($concatResults as $concatResult) {

			$vote = $concatResult[0];
			$voteCount = $concatResult[1];
			$ids = explode(',', $concatResult[2]);
			$entityTypes = explode(',', $concatResult[3]);
			$entityIds = explode(',', $concatResult[4]);

			$votes = $this->findByIds($ids);

			$votables = array();
			for ($i = 0 ; $i < $voteCount; ++$i) {

				// Retrive related entity
				$entityClassName = TypableUtils::getClassByType($entityTypes[$i]);
				if (is_null($entityClassName)) {
					continue;
				}
				$entityRepository = $this->getEntityManager()->getRepository($entityClassName);
				$votable = $entityRepository->findOneByIdJoinedOn($entityIds[$i], $entityRepository->getDefaultJoinOptions());
				if (is_null($votable)) {
					continue;
				}
				if ($votable instanceof HiddableInterface && !$votable->getIsPublic()) {
					continue;
				}

				$votables[] = $votable;
			}

			$items[] = array(
				'user'     => $vote->getUser(),
				'votes'    => $votes,
				'votables' => $votables,
			);

		}

		return $items;
	}

}