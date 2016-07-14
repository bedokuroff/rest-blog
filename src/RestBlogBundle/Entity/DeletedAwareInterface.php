<?php
namespace RestBlogBundle\Entity;

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