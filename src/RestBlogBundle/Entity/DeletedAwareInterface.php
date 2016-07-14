<?php
namespace RestBlogBundle\Entity;

/**
 * This interface is a part of 'Soft-Delete' implementation.
 */
interface DeletedAwareInterface
{
    /**
     * @param $deleted
     * @return DeletedAwareInterface
     */
    public function setDeleted($deleted);

    /**
     * @return boolean
     */
    public function isDeleted();
}