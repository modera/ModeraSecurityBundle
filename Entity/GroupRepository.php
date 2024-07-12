<?php

namespace Modera\SecurityBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * @author    Alex Plaksin <alex.plaksin@modera.net>
 * @copyright 2016 Modera Foundation
 */
class GroupRepository extends EntityRepository
{
    /**
     * Finds group by refName.
     *
     * Search is NOT case-sensitive because of unique constrain.
     *
     * @return Group[]
     */
    public function findByRefName(string $refName): array
    {
        /** @var Group[] $result */
        $result = $this->getEntityManager()
            ->createQuery('SELECT g FROM ModeraSecurityBundle:Group g WHERE LOWER(g.refName) = LOWER(:refName)')
                ->setParameter('refName', $refName)
            ->getResult()
        ;

        return $result;
    }
}
