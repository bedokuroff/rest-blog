<?php
namespace RestBlogBundle\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;

/**
 * In this custom repository we override several base fetching methods to make them aware of
 * 'deleted' field in the tables, as we use 'soft-delete' mechanics in our database.
 */
class DeletedAwareEntityRepository extends EntityRepository
{
    /**
     * @inheritdoc
     */
    public function find($id, $lockMode = null, $lockVersion = null)
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * @inheritdoc
     */
    public function findAll()
    {
        return $this->findBy([]);
    }

    /**
     * @inheritdoc
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $criteria['deleted'] = false;

        return parent::findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * @inheritdoc
     */
    public function findOneBy(array $criteria, array $orderBy = null)
    {
        $criteria['deleted'] = false;

        return parent::findOneBy($criteria, $orderBy);
    }

    /**
     * @inheritdoc
     */
    public function matching(Criteria $criteria)
    {
        $criteria->andWhere(Criteria::expr()->eq('deleted', false));

        return parent::matching($criteria);
    }

}