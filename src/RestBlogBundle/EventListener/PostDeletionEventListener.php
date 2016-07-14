<?php
namespace RestBlogBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;
use RestBlogBundle\Entity\Post;

class PostDeletionEventListener
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {

        $this->logger = $logger;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        /** @var Post $post */
        $post = $args->getEntity();
        if (!($post instanceof Post)) {
            return;
        }
        if ($post->isDeleted()) {
            $this->logger->info('The post was deleted. Post ID was:' . $args->getEntity()->getId());
        }
    }
}