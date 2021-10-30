<?php

namespace App\Repository\Knowledge;

use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Entity\Howto\Howto;
use App\Entity\Knowledge\Value\BaseValue;
use App\Entity\Knowledge\Value\Integer;
use App\Entity\Knowledge\Value\Picture;
use App\Entity\Knowledge\Value\Text;
use App\Entity\Knowledge\Provider;
use App\Entity\Wonder\Creation;
use App\Repository\Knowledge\Value\BaseValueRepository;

class ProviderRepository extends AbstractKnowledgeRepository {

	/////

	public function findUserIdsById($id) {
		return $this->getEntityManager()->getRepository(BaseValue::class)->findUserIdsByParentEntityTypeAndParentEntityId(Provider::TYPE, $id);
	}

	//////

	public function existsBySign($sign, $excludedId = 0) {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'count(p.id)' ))
			->from($this->getEntityName(), 'p')
			->where('LOWER(p.sign) = :sign')
			->setParameter('sign', strtolower($sign))
		;

		if ($excludedId > 0) {
			$queryBuilder
				->andWhere('p.id != :excludedId')
				->setParameter('excludedId', $excludedId)
			;
		}

		try {
			return $queryBuilder->getQuery()->getSingleScalarResult() > 0;
		} catch (\Doctrine\ORM\NoResultException $e) {
			return false;
		}
	}

	/////

	public function findOneByIdJoinedOnOptimized($id) {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'p', 'mp', 'logv' ))
			->from($this->getEntityName(), 'p')
			->leftJoin('p.mainPicture', 'mp')
			->leftJoin('p.logoValues', 'logv')
			->where('p.id = :id')
			->setParameter('id', $id)
		;

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
			->select(array( 'p', 'mp' ))
			->from($this->getEntityName(), 'p')
			->leftJoin('p.mainPicture', 'mp')
			->where($queryBuilder->expr()->in('p.id', $ids))
		;

		try {
			return $queryBuilder->getQuery()->getResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}

	/////

	public function findPagined($offset, $limit, $filter = 'recent') {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'p', 'mp' ))
			->from($this->getEntityName(), 'p')
			->innerJoin('p.mainPicture', 'mp')
			->where('p.isDraft = false')
			->setFirstResult($offset)
			->setMaxResults($limit)
		;

		$this->_applyCommonFilter($queryBuilder, $filter);

		return new Paginator($queryBuilder->getQuery());
	}

	private function _applyCommonFilter(&$queryBuilder, $filter) {
		if ('popular-views' == $filter) {
			$queryBuilder
				->addOrderBy('p.viewCount', 'DESC')
			;
		} else if ('popular-likes' == $filter) {
			$queryBuilder
				->addOrderBy('p.likeCount', 'DESC')
			;
		} else if ('popular-comments' == $filter) {
			$queryBuilder
				->addOrderBy('p.commentCount', 'DESC')
			;
		} else if ('collaborative-contributors' == $filter) {
			$queryBuilder
				->addOrderBy('p.contributorCount', 'DESC')
			;
		} else if ('order-alphabetical' == $filter) {
			$queryBuilder
				->addOrderBy('p.title', 'ASC')
			;
		}
		$queryBuilder
			->addOrderBy('p.changedAt', 'DESC')
		;
	}

	public function findPaginedByCreation(Creation $creation, $offset, $limit, $filter = 'recent') {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'p', 'mp', 'c' ))
			->from($this->getEntityName(), 'p')
			->leftJoin('p.mainPicture', 'mp')
			->innerJoin('p.creations', 'c')
			->where('c = :creation')
			->setParameter('creation', $creation)
			->setFirstResult($offset)
			->setMaxResults($limit)
		;

		$this->_applyCommonFilter($queryBuilder, $filter);

		return new Paginator($queryBuilder->getQuery());
	}

	public function findPaginedByHowto(Howto $howto, $offset, $limit, $filter = 'recent') {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'p', 'mp', 'h' ))
			->from($this->getEntityName(), 'p')
			->leftJoin('p.mainPicture', 'mp')
			->innerJoin('p.howtos', 'h')
			->where('h = :howto')
			->setParameter('howto', $howto)
			->setFirstResult($offset)
			->setMaxResults($limit)
		;

		$this->_applyCommonFilter($queryBuilder, $filter);

		return new Paginator($queryBuilder->getQuery());
	}

	public  function findGeocoded($filter = 'recent') {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'p', 'mp' ))
			->from($this->getEntityName(), 'p')
			->leftJoin('p.mainPicture', 'mp')
			->where('p.latitude IS NOT NULL')
			->andWhere('p.longitude IS NOT NULL')
		;

		$this->_applyCommonFilter($queryBuilder, $filter);

		try {
			return $queryBuilder->getQuery()->getResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}

}