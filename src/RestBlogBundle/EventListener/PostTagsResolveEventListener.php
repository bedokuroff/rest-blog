<?php
namespace RestBlogBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use RestBlogBundle\Entity\Post;
use RestBlogBundle\Entity\Tag;

/**
 * In this listener we want both prePersist and preUpdate events,
 * as according to doctrine flow, prePersist is called only on first
 * persist call, and we want it to be called also when the post is updated.
 */
class PostTagsResolveEventListener
{
    const TAG_ENTITY = 'RestBlogBundle:Tag';

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->doPrePersist($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->doPrePersist($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    private function doPrePersist(LifecycleEventArgs $args)
    {
        /** @var Post $post */
        $post = $args->getEntity();
        if (!($post instanceof Post)) {
            return;
        }
        $tagNames = [];
        /** @var Tag $tag */
        $postTags = $post->getTags();
        foreach ($postTags as $tag) {
            if (empty($tag->getId())) {
                $tagNames[] = $tag->getName();
            }
        }
        if (empty($tagNames)) {
            return;
        }
        $em = $args->getEntityManager();
        $query = "select t from ".self::TAG_ENTITY." t where t.name in (:names) and t.deleted = 0";
        $queryObj = $em->createQuery($query)->setParameter('names', $tagNames);
        $existingTags = $queryObj->getResult();
        if (empty($existingTags)) {
            return;
        }

        foreach ($existingTags as $existingTag) {
            foreach ($postTags as $postTag) {
                if ($postTag->getName() == $existingTag->getName()) {
                    $post->removeTag($postTag);
                    $post->addTag($existingTag);
                }
            }
        }
    }
}