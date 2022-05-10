<?php

namespace App\Controller;

use App\Entity\Product;
use JsonException;
use App\Repository\ProductRepository;
use App\Service\PaginationFactory;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\Get;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/products")
 */
class ProductController extends AbstractApiController
{
    protected $serializer;
    protected $productRepository;
    protected $pagination;

    public function __construct(SerializerInterface $serializer, ProductRepository $productRepository)
    {
        $this->serializer = $serializer;
        $this->productRepository = $productRepository;
        /*$this->pagination = $pagination;*/
    }
    /**
     * @Route(name="products_list", methods={"GET"})
     * @OA\Get(summary="Get list of BileMo products")
     * @OA\Response(
     *     response=JsonResponse::HTTP_OK,
     *     description="Returns the list of products"
     * )
     */
    public function list(Request $request, PaginationFactory $paginationFactory)/*:Response*/
    {
        $query = $this->productRepository->findAllQueryBuilder();

        $paginatedCollection = $paginationFactory->createCollection($query, $request, 'products_list', [], 5);
        //\dd($paginatedCollection);
        /*return new JsonResponse(
            $this->serializer->serialize($paginatedCollection,'json',["groups" =>"products:list"]),
            JsonResponse::HTTP_OK, [], true
        );*/
        return $this->respond($paginatedCollection,Response::HTTP_OK);
      

        /*return new JsonResponse(
            $this->serializer->serialize($this->productRepository->findAll(),"json", ["groups" => "products:list"]),
            JsonResponse::HTTP_OK, [], true
        );*/
    }

    /**
     * @Route("/{id}", name="api_products_details", methods={"GET"})
     * @return JsonResponse
     * @param Product $product
     */
    public function details(Product $product): JsonResponse
    {   
        if (!$product || !($product instanceof Product)) {
            throw new JsonException("Incorrect identifier or no product found with this identifier", JsonResponse::HTTP_NOT_FOUND);
        }
        return new JsonResponse(
            $this->serializer->serialize($product, "json", ["groups" => "product:details"]),
            JsonResponse::HTTP_OK, [], true
        );
    }
}
