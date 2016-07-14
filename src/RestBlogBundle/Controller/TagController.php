<?php
namespace RestBlogBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RestBlogBundle\Entity\Tag;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class TagController extends CRUDController
{
    protected $entity = 'RestBlogBundle:Tag';

    /**
     * @ApiDoc(
     *     section="Tag",
     *     resource=true,
     *     description="This method lists all the tags with paging. Returns the list of tags in case of success.",
     *     parameters={
     *          {"name"="limit", "dataType"="integer", "required"=false, "format"="GET", "description"="Limit of tags to pass to BD"},
     *          {"name"="offset", "dataType"="integer", "required"=false,"format"="GET", "description"="Offset to start the listing from"}
     *      },
     *     statusCodes={
     *          200="List of tags",
     *          400="There was an error getting tags",
     *          404="No tags were found"
     *     }
     *     )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function tagListAction(Request $request)
    {
        return $this->itemList($request);
    }

    /**
     *
     * @ApiDoc(
     *     section="Tag",
     *     resource=true,
     *     description="Deletes a tag",
     *     requirements={
     *          {"name"="tagName", "dataType"="string", "required"=true, "description"="The name of the tag"}
     *     },
     *     statusCodes={
     *          204="Request was successful and the tag was deleted",
     *          400="There was an error deleting tag",
     *          404="No tag was found with such name"
     *     }
     *     )
     *
     * @param Tag $tag
     * @ParamConverter("tag", options={"mapping": {"tagName": "name"}})
     * @return JsonResponse
     */
    public function deleteTagAction(Tag $tag)
    {
        return $this->deleteItem($tag);
    }

    /**
     * @ApiDoc(
     *     section="Tag",
     *     resource=true,
     *     description="Adds a tag",
     *     requirements={
     *          {"name"="tagName", "dataType"="string", "required"=true, "description"="The name of the tag"}
     *     },
     *     statusCodes={
     *          201="Request was successful and the tag was added",
     *          400="There was an error adding tag"
     *     }
     *     )
     *
     * @param $tagName
     * @return JsonResponse
     */
    public function addTagAction($tagName = null)
    {
        $repo = $this->getDoctrine()->getRepository($this->entity);
        try {
            $tag = $repo->findBy(['name' => $tagName]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
        if (!empty($tag)) {
            return new JsonResponse(['error' => 'Tag with this name already exists'], JsonResponse::HTTP_CONFLICT);
        }

        $tag = new Tag();
        $tag->setName($tagName);

        return $this->addSingleItem($tag);
    }

    /**
     * @ApiDoc(
     *     section="Tag",
     *     resource=true,
     *     description="This method provides posts count for the provided tags.",
     *     parameters={
     *           {"name"="tagNames", "dataType"="string", "required"=true, "description"="List of tags separated by ','"}
     *      },
     *     statusCodes={
     *          200="List of tags with counts",
     *          400="There was an error getting tags",
     *          404="No tags were found"
     *     }
     *     )
     *
     * @param $tagNames
     * 
     * @return JsonResponse
     */
    public function postsByTagCountAction($tagNames)
    {
        return $this->getTagsByName($tagNames, true);
    }

    /**
     * /**
     * @ApiDoc(
     *     section="Tag",
     *     resource=true,
     *     description="This method provides posts for the provided tags.",
     *     parameters={
     *           {"name"="tagNames", "dataType"="string", "required"=true, "description"="List of tags separated by ','"}
     *      },
     *     statusCodes={
     *          200="List of tags with posts",
     *          400="There was an error getting tags",
     *          404="No tags were found"
     *     }
     *     )
     *
     * @param $tagNames
     *
     * @return JsonResponse
     *
     */
    public function postsByTagAction($tagNames)
    {
        return $this->getTagsByName($tagNames, false);
    }

    /**
     * This method actually get the tags from the repository, with post list/post count attached to them.
     *
     * @param $tagName
     * @param bool $count
     *
     * @return JsonResponse
     */
    private function getTagsByName($tagName, $count = true)
    {
        $repo = $this->getDoctrine()->getRepository($this->entity);

        try {
            /** @var Tag $tag */
            $tags = $repo->findBy([
                'name' => strpos($tagName, Tag::SEPARATOR) === false ? $tagName : explode(Tag::SEPARATOR, $tagName)
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }

        if (empty($tags)) {
            return new JsonResponse('', JsonResponse::HTTP_NOT_FOUND);
        }

        $tagsArray = [];
        /** @var Tag $tag */
        foreach ($tags as $tag) {
            $tagsArray[$tag->getName()] = $count ? $tag->getPosts()->count() : $tag->getPosts()->toArray();
        }

        return new JsonResponse($tagsArray);
    }
}