<?php

namespace App\Repository\Howto;

use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Entity\Knowledge\Provider;
use App\Entity\Core\User;
use App\Entity\Knowledge\School;
use App\Entity\Qa\Question;
use App\Entity\Wonder\Creation;
use App\Entity\Wonder\Plan;
use App\Entity\Wonder\Workshop;
use App\Entity\Workflow\Workflow;
use App\Repository\AbstractEntityRepository;

class HowtoRepository extends AbstractEntityRepository {

	/////

	public function getDefaultJoinOptions() {
		return array( array( 'inner', 'user', 'u' ), array( 'left', 'mainPicture', 'mp' ) );
	}

	/////

	public function findOneByIdJoinedOnOptimized($id) {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'h', 'u', 'mp', 'ats', 'abbs'/*, 'pls', 'cts', 'wfs', 'wks'*/ ))
			->from($this->getEntityName(), 'h')
			->innerJoin('h.user', 'u')
			->innerJoin('h.mainPicture', 'mp')
			->leftJoin('h.articles', 'ats')
			->leftJoin('ats.bodyBlocks', 'abbs')
//			->leftJoin('h.plans', 'pls')		// Memory limit exceded
//			->leftJoin('h.creations', 'cts')
//			->leftJoin('h.workflows', 'wfs')
//			->leftJoin('h.workshops', 'wks')
			->where('h.id = :id')
			->setParameter('id', $id)
		;

		try {
			return $queryBuilder->getQuery()->getSingleResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}

	public function findOneFirstByUser(User $user) {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'h', 'u', 'mp' ))
			->from($this->getEntityName(), 'h')
			->innerJoin('h.user', 'u')
			->leftJoin('h.mainPicture', 'mp')
			->where('h.isDraft = false')
			->andWhere('h.user = :user')
			->orderBy('h.id', 'ASC')
			->setParameter('user', $user)
			->setMaxResults(1);

		try {
			return $queryBuilder->getQuery()->getSingleResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}

	public function findOneLastByUser(User $user) {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'h', 'u', 'mp' ))
			->from($this->getEntityName(), 'h')
			->innerJoin('h.user', 'u')
			->leftJoin('h.mainPicture', 'mp')
			->where('h.isDraft = false')
			->andWhere('h.user = :user')
			->orderBy('h.id', 'DESC')
			->setParameter('user', $user)
			->setMaxResults(1);

		try {
			return $queryBuilder->getQuery()->getSingleResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}

	public function findOnePreviousByUserAndId(User $user, $id) {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'h', 'u', 'mp' ))
			->from($this->getEntityName(), 'h')
			->innerJoin('h.user', 'u')
			->leftJoin('h.mainPicture', 'mp')
			->where('h.isDraft = false')
			->andWhere('h.user = :user')
			->andWhere('h.id < :id')
			->orderBy('h.id', 'DESC')
			->setParameter('user', $user)
			->setParameter('id', $id)
			->setMaxResults(1);

		try {
			return $queryBuilder->getQuery()->getSingleResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}

	public function findOneNextByUserAndId(User $user, $id) {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'h', 'u', 'mp' ))
			->from($this->getEntityName(), 'h')
			->innerJoin('h.user', 'u')
			->leftJoin('h.mainPicture', 'mp')
			->where('h.isDraft = false')
			->andWhere('h.user = :user')
			->andWhere('h.id > :id')
			->orderBy('h.id', 'ASC')
			->setParameter('user', $user)
			->setParameter('id', $id)
			->setMaxResults(1);

		try {
			return $queryBuilder->getQuery()->getSingleResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}

	/////

	public function findByIds(array $ids) {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'h', 'u', 'mp', 'sp' ))
			->from($this->getEntityName(), 'h')
			->innerJoin('h.user', 'u')
			->leftJoin('h.mainPicture', 'mp')
			->leftJoin('h.spotlight', 'sp')
			->where($queryBuilder->expr()->in('h.id', $ids))
		;

		try {
			return $queryBuilder->getQuery()->getResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}

	/////

	public function findPagined($offset, $limit, $filter = 'recent', $filterParam = null) {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'h', 'u', 'mp', 't' ))
			->from($this->getEntityName(), 'h')
			->innerJoin('h.user', 'u')
			->leftJoin('h.mainPicture', 'mp')
			->leftJoin('h.tags', 't')
			->where('h.isDraft = false')
			->setFirstResult($offset)
			->setMaxResults($limit)
		;

		if ('followed' == $filter) {
			$queryBuilder
				->innerJoin('u.followers', 'f', 'WITH', 'f.user = :filterParam:')
				->setParameter('filterParam', $filterParam);
		}

		$this->_applyCommonFilter($queryBuilder, $filter);

		return new Paginator($queryBuilder->getQuery());
	}

	private function _applyCommonFilter(&$queryBuilder, $filter) {
		if ('popular-views' == $filter) {
			$queryBuilder
				->addOrderBy('h.viewCount', 'DESC')
			;
		} else if ('popular-likes' == $filter) {
			$queryBuilder
				->addOrderBy('h.likeCount', 'DESC')
			;
		} else if ('popular-comments' == $filter) {
			$queryBuilder
				->addOrderBy('h.commentCount', 'DESC')
			;
		}
		$queryBuilder
			->addOrderBy('h.changedAt', 'DESC');
	}

	public function findPaginedByUser(User $user, $offset, $limit, $filter = 'recent', $includeDrafts = false) {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'h', 'u', 'mp', 't' ))
			->from($this->getEntityName(), 'h')
			->innerJoin('h.user', 'u')
			->leftJoin('h.mainPicture', 'mp')
			->leftJoin('h.tags', 't')
			->where('h.user = :user')
			->setParameter('user', $user)
			->setFirstResult($offset)
			->setMaxResults($limit)
		;

		if ('draft' == $filter && $includeDrafts) {
			$queryBuilder
				->andWhere('h.isDraft = true')
			;
		} else if (!$includeDrafts) {
			$queryBuilder
				->andWhere('h.isDraft = false')
			;
		}

		$this->_applyCommonFilter($queryBuilder, $filter);

		return new Paginator($queryBuilder->getQuery());
	}

	public function findPaginedByQuestion(Question $question, $offset, $limit, $filter = 'recent') {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'h', 'u', 'mp', 'q' ))
			->from($this->getEntityName(), 'h')
			->innerJoin('h.user', 'u')
			->leftJoin('h.mainPicture', 'mp')
			->innerJoin('h.questions', 'q')
			->where('h.isDraft = false')
			->andWhere('q = :question')
			->setParameter('question', $question)
			->setFirstResult($offset)
			->setMaxResults($limit)
		;

		$this->_applyCommonFilter($queryBuilder, $filter);

		return new Paginator($queryBuilder->getQuery());
	}

	public function findPaginedByCreation(Creation $creation, $offset, $limit, $filter = 'recent') {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'h', 'u', 'mp', 'c' ))
			->from($this->getEntityName(), 'h')
			->innerJoin('h.user', 'u')
			->leftJoin('h.mainPicture', 'mp')
			->innerJoin('h.creations', 'c')
			->where('h.isDraft = false')
			->andWhere('c = :creation')
			->setParameter('creation', $creation)
			->setFirstResult($offset)
			->setMaxResults($limit)
		;

		$this->_applyCommonFilter($queryBuilder, $filter);

		return new Paginator($queryBuilder->getQuery());
	}

	public function findPaginedByPlan(Plan $plan, $offset, $limit, $filter = 'recent') {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'h', 'u', 'mp', 'p' ))
			->from($this->getEntityName(), 'h')
			->innerJoin('h.user', 'u')
			->leftJoin('h.mainPicture', 'mp')
			->innerJoin('h.plans', 'p')
			->where('h.isDraft = false')
			->andWhere('p = :plan')
			->setParameter('plan', $plan)
			->setFirstResult($offset)
			->setMaxResults($limit)
		;

		$this->_applyCommonFilter($queryBuilder, $filter);

		return new Paginator($queryBuilder->getQuery());
	}

	public function findPaginedByWorkflow(Workflow $workflow, $offset, $limit, $filter = 'recent') {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'h', 'u', 'mp', 'w' ))
			->from($this->getEntityName(), 'h')
			->innerJoin('h.user', 'u')
			->leftJoin('h.mainPicture', 'mp')
			->innerJoin('h.workflows', 'w')
			->where('h.isDraft = false')
			->andWhere('w = :workflow')
			->setParameter('workflow', $workflow)
			->setFirstResult($offset)
			->setMaxResults($limit)
		;

		$this->_applyCommonFilter($queryBuilder, $filter);

		return new Paginator($queryBuilder->getQuery());
	}

	public function findPaginedByWorkshop(Workshop $workshop, $offset, $limit, $filter = 'recent') {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'h', 'u', 'mp', 'w' ))
			->from($this->getEntityName(), 'h')
			->innerJoin('h.user', 'u')
			->leftJoin('h.mainPicture', 'mp')
			->innerJoin('h.workshops', 'w')
			->where('h.isDraft = false')
			->andWhere('w = :workshop')
			->setParameter('workshop', $workshop)
			->setFirstResult($offset)
			->setMaxResults($limit)
		;

		$this->_applyCommonFilter($queryBuilder, $filter);

		return new Paginator($queryBuilder->getQuery());
	}

	public function findPaginedByProvider(Provider $provider, $offset, $limit, $filter = 'recent') {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'h', 'u', 'mp', 'p' ))
			->from($this->getEntityName(), 'h')
			->innerJoin('h.user', 'u')
			->leftJoin('h.mainPicture', 'mp')
			->innerJoin('h.providers', 'p')
			->where('h.isDraft = false')
			->andWhere('p = :provider')
			->setParameter('provider', $provider)
			->setFirstResult($offset)
			->setMaxResults($limit)
		;

		$this->_applyCommonFilter($queryBuilder, $filter);

		return new Paginator($queryBuilder->getQuery());
	}

	public function findPaginedBySchool(School $school, $offset, $limit, $filter = 'recent') {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'h', 'u', 'mp', 's' ))
			->from($this->getEntityName(), 'h')
			->innerJoin('h.user', 'u')
			->leftJoin('h.mainPicture', 'mp')
			->innerJoin('h.schools', 's')
			->where('h.isDraft = false')
			->andWhere('s = :school')
			->setParameter('school', $school)
			->setFirstResult($offset)
			->setMaxResults($limit)
		;

		$this->_applyCommonFilter($queryBuilder, $filter);

		return new Paginator($queryBuilder->getQuery());
	}

}