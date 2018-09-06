<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    public function search($params = null)
    {
        $params = $params === null ? [] : $params;
        $builder = $this->createQueryBuilder('u');

        if (isset($params['name'])) {
            $builder
                ->andWhere('u.name LIKE :name')
                ->setParameter('name', '%'.$params['name'].'%');
        }

        if (isset($params['lastName'])) {
            $builder
                ->andWhere('u.lastName LIKE :lastName')
                ->setParameter('lastName', '%'.$params['lastName'].'%');
        }

        if (isset($params['email'])) {
            $builder
                ->andWhere('u.email LIKE :email')
                ->setParameter('email', '%'.$params['email'].'%');
        }

        if (isset($params['user'])) {
            $myFriends = $this
                ->createQueryBuilder('m')
                ->select('f.id')
                ->innerJoin('m.friends', 'f')
                ->andWhere('m.id = :me')
                ->setParameter('me', $params['user'])
                ->getQuery()->getResult();

            $builder
                ->andWhere('u.id != :user')
                ->setParameter('user', $params['user'])
                ->andWhere($builder->expr()->notIn(
                    'u.id',
                    empty(array_column($myFriends, 'id')) ? ['u.id'] : array_column($myFriends, 'id')
                ));
        }

        $result = $builder
            ->getQuery()
            ->getResult();

        return $result;
    }
}