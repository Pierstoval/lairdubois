<?php

namespace App\Repository\Core;

use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Entity\Core\Activity\AbstractActivity;
use App\Entity\Core\User;
use App\Repository\AbstractEntityRepository;

class NotificationRepository extends AbstractEntityRepository {

	/////

	public function getDefaultJoinOptions() {
		return array( array( 'inner', 'activity', 'a' ) );
	}

	/////

	public function countUnlistedByUser(User $user) {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'count(n.id)' ))
			->from($this->getEntityName(), 'n')
			->where('n.user = :user')
			->andWhere('n.isListed = 0')
			->setParameter('user', $user)
		;

		try {
			return $queryBuilder->getQuery()->getSingleScalarResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}

	/////

	public function findOneByIdJoinedOnActivity($id) {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'n', 'a' ))
			->from($this->getEntityName(), 'n')
			->innerJoin('n.activity', 'a')
			->where('n.id = :id')
			->setParameter('id', $id)
		;

		try {
			return $queryBuilder->getQuery()->getSingleResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}

	public function findOneByActivity(AbstractActivity $activity) {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'n' ))
			->from($this->getEntityName(), 'n')
			->where('n.activity = :activity')
			->setParameter('activity', $activity)
		;

		try {
			return $queryBuilder->getQuery()->getSingleResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}

	/////

	public function findByNewerThanAndGroupIdentifierAndUser($date, $groupIdentifier, $user) {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'n', 'u' ))
			->from($this->getEntityName(), 'n')
			->innerJoin('n.user', 'u')
			->where('n.createdAt > :date')
			->andWhere('n.groupIdentifier = :groupIdentifier')
			->andWhere('n.user = :user')
			->orderBy('n.id', 'DESC')
			->setParameter('date', $date)
			->setParameter('groupIdentifier', $groupIdentifier)
			->setParameter('user', $user)
		;

		try {
			return $queryBuilder->getQuery()->getResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}

	}

	public function findByPendingEmailAndActivityInstanceOf($activityInstanceOf) {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'n', 'a', 'u' ))
			->from($this->getEntityName(), 'n')
			->innerJoin('n.user', 'u')
			->innerJoin('n.activity', 'a')
			->where('n.isPendingEmail = 1')
			->andWhere('n.isListed = 0')
			->andWhere('a INSTANCE OF '.$activityInstanceOf)
			->orderBy('u.id', 'ASC')
		;

		try {
			return $queryBuilder->getQuery()->getResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}

	}

	/////

	public function findPaginedByUser(User $user, $offset, $limit, $filter = 'recent') {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'n', 'a', 'au' ))
			->from($this->getEntityName(), 'n')
			->innerJoin('n.activity', 'a')
			->innerJoin('a.user', 'au')
			->where('n.user = :user')
			->andWhere('n.folder IS NULL')
			->setParameter('user', $user)
			->orderBy('a.id', 'DESC')
			->setFirstResult($offset)
			->setMaxResults($limit)
		;

		$this->_applyCommonFilter($queryBuilder, $filter);

		return new Paginator($queryBuilder->getQuery());
	}

	private function _applyCommonFilter(&$queryBuilder, $filter) {
		if ('activity-mention' == $filter) {
			$queryBuilder
				->andWhere('a INSTANCE OF App\\Entity\\Core\\Activity\\Mention')
			;
		} elseif ('activity-like' == $filter) {
			$queryBuilder
				->andWhere('a INSTANCE OF App\\Entity\\Core\\Activity\\Like')
			;
		} elseif ('activity-comment' == $filter) {
			$queryBuilder
				->andWhere('a INSTANCE OF App\\Entity\\Core\\Activity\\Comment')
			;
		} elseif ('activity-follow' == $filter) {
			$queryBuilder
				->andWhere('a INSTANCE OF App\\Entity\\Core\\Activity\\Follow')
			;
		} elseif ('activity-publish' == $filter) {
			$queryBuilder
				->andWhere('a INSTANCE OF App\\Entity\\Core\\Activity\\Publish')
			;
		} elseif ('activity-vote' == $filter) {
			$queryBuilder
				->andWhere('a INSTANCE OF App\\Entity\\Core\\Activity\\Vote')
			;
		} elseif ('activity-join' == $filter) {
			$queryBuilder
				->andWhere('a INSTANCE OF App\\Entity\\Core\\Activity\\Join')
			;
		} elseif ('activity-answer' == $filter) {
			$queryBuilder
				->andWhere('a INSTANCE OF App\\Entity\\Core\\Activity\\Answer')
			;
		} elseif ('activity-testify' == $filter) {
			$queryBuilder
				->andWhere('a INSTANCE OF App\\Entity\\Core\\Activity\\Testify')
			;
		} elseif ('activity-review' == $filter) {
			$queryBuilder
				->andWhere('a INSTANCE OF App\\Entity\\Core\\Activity\\Review')
			;
		} elseif ('activity-feedback' == $filter) {
			$queryBuilder
				->andWhere('a INSTANCE OF App\\Entity\\Core\\Activity\\Feedback')
			;
		} elseif ('activity-invite' == $filter) {
			$queryBuilder
				->andWhere('a INSTANCE OF App\\Entity\\Core\\Activity\\Invite')
			;
		} elseif ('activity-request' == $filter) {
			$queryBuilder
				->andWhere('a INSTANCE OF App\\Entity\\Core\\Activity\\Request')
			;
		}
		$queryBuilder
			->addOrderBy('a.createdAt', 'DESC')
		;
	}

}