<?php

namespace App\Repository\Find;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Repository\AbstractEntityRepository;
use App\Entity\Find\Find;
use App\Entity\Core\User;

class FindRepository extends AbstractEntityRepository {

	/////

	public function getDefaultJoinOptions() {
		return array( array( 'inner', 'user', 'u' ) );
	}

	/////

	public function existsByWebsiteUrl($url, $excludedId = 0) {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'count(w.id)' ))
			->from('App\Entity\Find\Content\Website', 'w')
			->where('w.url = :url')
			->setParameter('url', $url)
		;
		if ($excludedId != 0) {
			$queryBuilder
				->andWhere('w.id <> :excludedId')
				->setParameter('excludedId', $excludedId)
			;
		}

		try {
			if ($queryBuilder->getQuery()->getSingleScalarResult() > 0) {
				return true;
			}
		} catch (NonUniqueResultException $e) {
			return false;
		}

		return false;
	}

	public function existsByVideoKindAndEmbedIdentifier($kind, $embedIdentifier, $excludedId = 0) {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'count(v.id)' ))
			->from('App\Entity\Find\Content\Video', 'v')
			->where('v.kind = :kind')
			->andWhere('v.embedIdentifier = :embedIdentifier')
			->setParameter('kind', $kind)
			->setParameter('embedIdentifier', $embedIdentifier)
		;
		if ($excludedId != 0) {
			$queryBuilder
				->andWhere('v.id <> :excludedId')
				->setParameter('excludedId', $excludedId)
			;
		}

		try {
			if ($queryBuilder->getQuery()->getSingleScalarResult() > 0) {
				return true;
			}
		} catch (NonUniqueResultException $e) {
			return false;
		}

		return false;
	}


	/////

	public function findOneByIdJoinedOnUser($id) {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'f', 'u' ))
			->from($this->getEntityName(), 'f')
			->innerJoin('f.user', 'u')
			->where('f.id = :id')
			->setParameter('id', $id)
		;

		try {
			return $queryBuilder->getQuery()->getSingleResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}

	public function findOneByIdJoinedOnOptimized($id) {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'f', 'u', 'uav', 'mp', 'bbs', 'ct', 'tgs' ))
			->from($this->getEntityName(), 'f')
			->innerJoin('f.user', 'u')
			->innerJoin('u.avatar', 'uav')
			->leftJoin('f.mainPicture', 'mp')
			->leftJoin('f.bodyBlocks', 'bbs')
			->leftJoin('f.content', 'ct')
			->leftJoin('f.tags', 'tgs')
			->where('f.id = :id')
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
			->select(array( 'f', 'u', 'mp' ))
			->from($this->getEntityName(), 'f')
			->innerJoin('f.user', 'u')
			->leftJoin('f.mainPicture', 'mp')
			->where('f.isDraft = false')
			->andWhere('f.user = :user')
			->orderBy('f.id', 'ASC')
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
			->select(array( 'f', 'u', 'mp' ))
			->from($this->getEntityName(), 'f')
			->innerJoin('f.user', 'u')
			->leftJoin('f.mainPicture', 'mp')
			->where('f.isDraft = false')
			->andWhere('f.user = :user')
			->orderBy('f.id', 'DESC')
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
			->select(array( 'f', 'u', 'mp' ))
			->from($this->getEntityName(), 'f')
			->innerJoin('f.user', 'u')
			->leftJoin('f.mainPicture', 'mp')
			->where('f.isDraft = false')
			->andWhere('f.user = :user')
			->andWhere('f.id < :id')
			->orderBy('f.id', 'DESC')
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
			->select(array( 'f', 'u', 'mp' ))
			->from($this->getEntityName(), 'f')
			->innerJoin('f.user', 'u')
			->leftJoin('f.mainPicture', 'mp')
			->where('f.isDraft = false')
			->andWhere('f.user = :user')
			->andWhere('f.id > :id')
			->orderBy('f.id', 'ASC')
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
			->select(array( 'f', 'u', 'mp', 'ct' ))
			->from($this->getEntityName(), 'f')
			->innerJoin('f.user', 'u')
			->innerJoin('f.mainPicture', 'mp')
			->leftJoin('f.content', 'ct')
			->where($queryBuilder->expr()->in('f.id', $ids))
		;

		try {
			return $queryBuilder->getQuery()->getResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}

	/////

	private function _applyCommonFilter(&$queryBuilder, $filter) {
		if ('popular-views' == $filter) {
			$queryBuilder
				->addOrderBy('f.viewCount', 'DESC')
			;
		} else if ('popular-likes' == $filter) {
			$queryBuilder
				->addOrderBy('f.likeCount', 'DESC')
			;
		} else if ('popular-comments' == $filter) {
			$queryBuilder
				->addOrderBy('f.commentCount', 'DESC')
			;
		}
		$queryBuilder
			->addOrderBy('f.changedAt', 'DESC')
		;
	}

	public function findPagined($offset, $limit, $filter = 'recent') {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'f', 'u', 'mp', 'c' ))
			->from($this->getEntityName(), 'f')
			->innerJoin('f.user', 'u')
			->leftJoin('f.mainPicture', 'mp')
			->innerJoin('f.content', 'c')
			->where('f.isDraft = false')
			->setFirstResult($offset)
			->setMaxResults($limit)
		;

		$this->_applyCommonFilter($queryBuilder, $filter);

		return new Paginator($queryBuilder->getQuery());
	}

	public function findPaginedByUser(User $user, $offset, $limit, $filter = 'recent', $includeDrafts = false) {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'f', 'u', 'mp' ))
			->from($this->getEntityName(), 'f')
			->innerJoin('f.user', 'u')
			->leftJoin('f.mainPicture', 'mp')
			->innerJoin('f.content', 'c')
			->where('u = :user')
			->setParameter('user', $user)
			->setFirstResult($offset)
			->setMaxResults($limit)
		;

		if ('draft' == $filter && $includeDrafts) {
			$queryBuilder
				->andWhere('f.isDraft = true')
			;
		} else if (!$includeDrafts) {
			$queryBuilder
				->andWhere('f.isDraft = false')
			;
		}

		$this->_applyCommonFilter($queryBuilder, $filter, true);

		return new Paginator($queryBuilder->getQuery());
	}

}