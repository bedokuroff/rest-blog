<?php
namespace RestBlogBundle\Mail;

use RestBlogBundle\Exception\SendMailException;
use Symfony\Component\Templating\EngineInterface;

class SendMailService
{
    /**
     * @var EngineInterface
     */
    private $renderer;
    /**
     * @var \Swift_Mailer
     */
    private $mailer;
    /**
     * @var string
     */
    private $mailFrom;
    /**
     * @var string
     */
    private $mailTo;

    /**
     * @param EngineInterface $renderer
     * @param \Swift_Mailer $mailer
     * @param string $mailFrom
     * @param string $mailTo
     */
    public function __construct(EngineInterface $renderer, \Swift_Mailer $mailer, $mailFrom, $mailTo)
    {
        $this->renderer = $renderer;
        $this->mailer = $mailer;
        $this->mailFrom = $mailFrom;
        $this->mailTo = $mailTo;
    }

    /**
     * @param $postContent
     * @throws SendMailException
     */
    public function sendNewPostNotification($postContent)
    {
        $renderedMail = $this->renderer->render('RestBlogBundle::email.txt.twig', ['post' => $postContent]);

        $message = \Swift_Message::newInstance()
            ->setSubject('Notification about new post')
            ->setFrom($this->mailFrom)
            ->setTo($this->mailTo)
            ->setBody($renderedMail, 'text/plain');
        $result = $this->mailer->send($message);

        if ($result == 0) {
            throw new SendMailException('There was an error sending email.');
        }
    }
}