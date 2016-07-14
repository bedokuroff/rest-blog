<?php
namespace RestBlogBundle\Controller;

use JsonSerializable;
use RestBlogBundle\Entity\DeletedAwareInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class CRUDController extends Controller
{
    protected $entity = '';
    protected $listOrderBy = null;

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    protected function itemList(Request $request)
    {
        $limit = $request->query->get('limit', $this->getParameter('rest_blog.db.default_limit'));
        $offset = $request->query->get('offset', $this->getParameter('rest_blog.db.default_offset'));
        $repo = $this->getDoctrine()->getRepository($this->entity);

        try {
            $itemList = $repo->findBy([], $this->listOrderBy, $limit, $offset);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }

        if (empty($itemList)) {
            return new JsonResponse([], JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse($itemList);
    }

    /**
     * @param JsonSerializable $item
     *
     * @return JsonResponse
     */
    protected function viewSingleItem(JsonSerializable $item)
    {
        return new JsonResponse($item);
    }

    /**
     * @param mixed $item
     *
     * @return JsonResponse
     */
    protected function addSingleItem($item)
    {
        $em = $this->getDoctrine()->getManager();
        $em->persist($item);

        try {
            $em->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }

        return new JsonResponse('', JsonResponse::HTTP_CREATED);
    }

    /**
     * @param DeletedAwareInterface $item
     *
     * @return JsonResponse
     */
    protected function deleteItem(DeletedAwareInterface $item)
    {
        $item->setDeleted(true);

        try {
            $this->getDoctrine()->getManager()->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }

        return new JsonResponse('', JsonResponse::HTTP_NO_CONTENT);
    }
}