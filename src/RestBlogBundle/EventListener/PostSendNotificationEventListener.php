<?php
namespace RestBlogBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use RestBlogBundle\Entity\Post;

/**
 * This class exists to send the newly-added post to queue to be
 * consumed by email-sending consumer (or something different if needed)
 */
class PostSendNotificationEventListener
{
    /**
     * @var ProducerInterface
     */
    private $producer;

    public function __construct(ProducerInterface $producer)
    {
        $this->producer = $producer;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $post = $args->getEntity();
        if (!($post instanceof Post)) {
            return;
        }

        $this->producer->publish(json_encode($post));
    }
}