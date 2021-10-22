<?php

namespace App\Repository\Core;

use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Entity\Core\User;
use App\Repository\AbstractEntityRepository;

class MemberRepository extends AbstractEntityRepository {

	//////

	public function existsByTeamAndUser(User $team, User $user) {
		$query = $this->getEntityManager()
			->createQuery('
                SELECT count(e.id) FROM App\Entity\Core\Member e
                WHERE e.team = :team AND e.user = :user
            ')
			->setParameter('team', $team)
			->setParameter('user', $user);

		try {
			return $query->getSingleScalarResult() > 0;
		} catch (\Doctrine\ORM\NoResultException $e) {
			return false;
		}
	}

	/////

	public function findOneByTeamAndUser(User $team, User $user) {
		$query = $this->getEntityManager()
			->createQuery('
                SELECT e FROM App\Entity\Core\Member e
                WHERE e.team = :team AND e.user = :user
            ')
			->setParameter('team', $team)
			->setParameter('user', $user);

		try {
			return $query->getSingleResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}

	public function findByTeam(User $team) {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'e', 'u' ))
			->from($this->getEntityName(), 'e')
			->innerJoin('e.user', 'u')
			->where('e.team = :team')
			->setParameter('team', $team);

		try {
			return $queryBuilder->getQuery()->getResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}

	//////

	private function _applyCommonFilter(&$queryBuilder, $filter) {
		$queryBuilder
			->addOrderBy('u.createdAt', 'DESC')
		;
	}

	public function findPaginedByUser(User $user, $offset = 0, $limit = 0, $filter = 'recent') {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'e', 'u', 'm' ))
			->from($this->getEntityName(), 'e')
			->innerJoin('e.team', 'u')
			->innerJoin('u.meta', 'm')
			->where('e.user = :user')
			->setParameter('user', $user)
		;

		if ($offset > 0) {
			$queryBuilder->setFirstResult($offset);
		}
		if ($limit > 0) {
			$queryBuilder->setMaxResults($limit);
		}

		$this->_applyCommonFilter($queryBuilder, $filter);

		return new Paginator($queryBuilder->getQuery());
	}

	public function findPaginedByTeam(User $team, $offset = 0, $limit = 0, $filter = 'recent') {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'e', 'u', 'm' ))
			->from($this->getEntityName(), 'e')
			->innerJoin('e.user', 'u')
			->innerJoin('u.meta', 'm')
			->where('e.team = :team')
			->setParameter('team', $team)
		;

		if ($offset > 0) {
			$queryBuilder->setFirstResult($offset);
		}
		if ($limit > 0) {
			$queryBuilder->setMaxResults($limit);
		}

		$this->_applyCommonFilter($queryBuilder, $filter);

		return new Paginator($queryBuilder->getQuery());
	}

}