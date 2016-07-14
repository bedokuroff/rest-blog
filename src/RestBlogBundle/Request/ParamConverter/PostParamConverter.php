<?php
namespace RestBlogBundle\Request\ParamConverter;

use RestBlogBundle\Entity\Post;
use RestBlogBundle\Entity\Tag;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * We use this Param Converter to make sure our controller actions receive the entities
 * created from data incoming from the request without having to create entities by themselves.
 */
class PostParamConverter implements ParamConverterInterface
{
    const DEFAULT_AUTHOR_NAME = 'Anonymous';

    /**
     * @inheritDoc
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $postData = json_decode($request->getContent());

        if (empty($postData->title) || empty($postData->content)) {
            throw new NotFoundHttpException();
        }

        $postEntity = new Post();
        $postEntity->setContent($postData->content)
            ->setAuthor(!empty($postData->author) ? $postData->author : self::DEFAULT_AUTHOR_NAME)
            ->setTitle($postData->title);

        if (!empty ($postData->tags)) {
            foreach ($postData->tags as $tag) {
                $tagEntity = new Tag();
                $tagEntity->setName($tag);
                $postEntity->addTag($tagEntity);
            }
        }

        $request->attributes->set($configuration->getName(), $postEntity);
    }

    /**
     * @inheritDoc
     */
    public function supports(ParamConverter $configuration)
    {
        if ($configuration->getName() == 'incomingPost') {
            return true;
        }

        return false;
    }
}