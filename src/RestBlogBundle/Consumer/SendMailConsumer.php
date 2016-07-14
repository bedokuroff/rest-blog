<?php
namespace RestBlogBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use RestBlogBundle\Exception\SendMailException;
use RestBlogBundle\Mail\SendMailService;

/**
 * To keep things decoupled, we just receive
 * the message and send it to send mail service
 * which actually does the sending.
 */
class SendMailConsumer implements ConsumerInterface
{
    /**
     * @var SendMailService
     */
    private $sendMailService;

    public function __construct(SendMailService $sendMailService)
    {
        $this->sendMailService = $sendMailService;
    }

    /**
     * @inheritdoc
     */
    public function execute(AMQPMessage $msg)
    {
        // if the email is not sent, we leave the message in the queue
        try {
            $this->sendMailService->sendNewPostNotification($msg->getBody());
        } catch (SendMailException $e) {
            return false;
        }
    }
}