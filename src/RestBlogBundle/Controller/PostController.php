<?php
namespace RestBlogBundle\Controller;

use Doctrine\Common\Collections\Criteria;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RestBlogBundle\Entity\Post;
use RestBlogBundle\Entity\Tag;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PostController extends CRUDController
{
    protected $entity = 'RestBlogBundle:Post';
    protected $listOrderBy = ['updated' => Criteria::DESC];

    /**
     * @ApiDoc(
     *     section="Post",
     *     resource=true,
     *     description="This method lists all the posts with paging. Returns the list of posts in case of success.",
     *     parameters={
     *          {"name"="limit", "dataType"="integer", "required"=false, "format"="GET", "description"="Limit of posts to pass to BD"},
     *          {"name"="offset", "dataType"="integer", "required"=false,"format"="GET", "description"="Offset to start the listing from"}
     *      },
     *     statusCodes={
     *          200="List of posts",
     *          400="There was an error getting posts",
     *          404="No posts were found"
     *     }
     *     )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function postListAction(Request $request)
    {
        return $this->itemList($request);
    }

    /**
     * @ApiDoc(
     *     section="Post",
     *     resource=true,
     *     description="This method returns single post",
     *     requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="An ID of the post"}
     *     },
     *     statusCodes={
     *          200="Single post with fields.",
     *          400="There was an error getting post",
     *          404="No posts were found"
     *     },
     *     output="RestBlogBundle\Entity\Post"
     *     )
     *
     * @ParamConverter("post")
     *
     * @param Post $post
     * @return JsonResponse
     */
    public function viewSinglePostAction(Post $post)
    {
        return $this->viewSingleItem($post);
    }

    /**
     * @ApiDoc(
     *     section="Post",
     *     resource=true,
     *     description="This method creates a post from provided JSON.",
     *     parameters={
     *          {"name"="post", "dataType"="string", "required"=true, "format"="POST body", "description"="JSON object with fields 'title' (required), 'content' (required), 'author' and 'tags' (array of tag strings)."}
     *      },
     *     statusCodes={
     *          201="Request was successful and the post was created",
     *          400="There was an error creating the post",
     *     }
     *     )
     *
     * @param Post $incomingPost
     * @ParamConverter("incomingPost")
     * @return JsonResponse
     */
    public function addPostAction($incomingPost)
    {
        return $this->addSingleItem($incomingPost);
    }

    /**
     * @ApiDoc(
     *     section="Post",
     *     resource=true,
     *     description="Deletes a post",
     *     requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="An ID of the post"}
     *     },
     *     statusCodes={
     *          204="Request was successful and the post was deleted",
     *          400="There was an error deleting post",
     *          404="No posts were found"
     *     }
     *     )
     *
     * @ParamConverter("post")
     *
     * @param Post $post
     * @return JsonResponse
     */
    public function deletePostAction(Post $post)
    {
        return $this->deleteItem($post);
    }

    /**
     * @ApiDoc(
     *     section="Post",
     *     resource=true,
     *     description="This method replaces a post with provided ID with a post from provided JSON.",
     *     requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="An ID of the post"}
     *     },
     *     parameters={
     *          {"name"="post", "dataType"="string", "required"=true, "format"="POST body", "description"="JSON object with fields 'title' (required), 'content' (required), 'author' and 'tags' (array of tag strings)."}
     *      },
     *     statusCodes={
     *          201="Request was successful and the post was updated",
     *          400="There was an error updating the post",
     *     }
     *     )
     *
     * @ParamConverter("post")
     * @ParamConverter("incomingPost")
     *
     * @return JsonResponse
     */
    public function editPostAction(Post $post, $incomingPost)
    {
        // here we just update the fields
        /** @var Post $incomingPost */
        $post->setAuthor($incomingPost->getAuthor())
            ->setContent($incomingPost->getContent())
            ->setTitle($incomingPost->getTitle())
            ->setUpdated(new \DateTime());

        // in this cycle we compare the existing tags and new tags
        /** @var Tag $existingTag */
        foreach ($post->getTags() as $existingTag) {
            $keepTag = false;
            /** @var Tag $incomingTag */
            foreach ($incomingPost->getTags() as $incomingTag) {
                // this tag exists already, so we remove it from new tags which are to be added later
                if ($incomingTag->getName() == $existingTag->getName()) {
                    $incomingPost->removeTag($incomingTag);
                    $keepTag = true;
                }
            }
            // we did not found the existing tag in the new tags array, so we remove existing tag
            if (!$keepTag) {
                $post->removeTag($existingTag);
            }
        }
        // here we add the new tags which were not present already
        foreach($incomingPost->getTags() as $incomingTag) {
            $post->addTag($incomingTag);
        }

        try {
            $this->getDoctrine()->getManager()->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }

        return new JsonResponse('', JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * @ApiDoc(
     *     section="Post",
     *     resource=true,
     *     description="This method adds tags to the post.",
     *     requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="An ID of the post"},
     *          {"name"="tagNames", "dataType"="string", "required"=true, "description"="List of tags separated by ','"}
     *     },
     *     statusCodes={
     *          201="Request was successful and the post was updated",
     *          400="There was an error updating the post",
     *     }
     *     )
     *
     * @ParamConverter("post")
     *
     * @param Post $post
     * @param string $tagNames
     *
     * @return JsonResponse
     */
    public function addTagsToPostAction(Post $post, $tagNames = null)
    {
        if (is_null($tagNames)) {
            return new JsonResponse(['error' => 'No tags provided'], JsonResponse::HTTP_BAD_REQUEST);
        }
        $existingTagsArray = [];
        /** @var Tag $tag */
        foreach ($post->getTags()->toArray() as $tag) {
            $existingTagsArray[] = $tag->getName();
        }

        $newTagsArray = explode(Tag::SEPARATOR, $tagNames);
        $tagsToAdd = array_diff($newTagsArray, $existingTagsArray);
        foreach ($tagsToAdd as $tag)
        {
            $tagEntity = new Tag();
            $tagEntity->setName($tag);
            $post->addTag($tagEntity);
        }

        try {
            $this->getDoctrine()->getManager()->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }

        return new JsonResponse('', JsonResponse::HTTP_NO_CONTENT);
    }
}