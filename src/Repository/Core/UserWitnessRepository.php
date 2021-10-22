<?php

namespace App\Repository\Core;

use Doctrine\ORM\Tools\Pagination\Paginator;
use App\Entity\Core\User;
use App\Repository\AbstractEntityRepository;

class UserWitnessRepository extends AbstractEntityRepository {

	/////

	public function existsNewerByUserFromDate(User $user, $date) {
		$queryBuilder = $this->getEntityManager()->createQueryBuilder();
		$queryBuilder
			->select(array( 'count(e.id)' ))
			->from($this->getEntityName(), 'e')
			->where('e.user = :user')
			->andWhere('e.createdAt > :date')
			->setParameter('user', $user)
			->setParameter('date', $date)
		;
		try {
			return $queryBuilder->getQuery()->getSingleScalarResult() > 0;
		} catch (\Doctrine\ORM\NoResultException $e) {
			return false;
		}
	}


}