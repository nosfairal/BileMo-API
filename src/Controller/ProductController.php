<?php

namespace App\Controller;

use App\Entity\Product;
use JsonException;
use App\Repository\ProductRepository;
use App\Service\PaginationFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\Get;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
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
     * @IsGranted("ROLE_USER")
     * @Cache(maxage="1 hour", public=true)
     * @OA\Get(summary="Get list of BileMo products")
     * @OA\Response(
     *     response=JsonResponse::HTTP_OK,
     *     description="Returns the list of products",
     *     @Model(type=Product::class)
     * )
     * @OA\Response(
     *     response=JsonResponse::HTTP_NOT_FOUND,
     *     description="The page you asked doesn't exist"
     * )
     * @OA\Parameter(
     *          name="page",
     *          in="query",
     *          description="Number of the page you want to see",
     *          example=4,
     *      )
     * @OA\Tag(name="Products")
     */
    public function list(Request $request, PaginationFactory $paginationFactory, $page= null)/*:Response*/
    {
        $query = $this->productRepository->findAllQueryBuilder();
        $page = $request->get('page');
        if($page > 16){
            return new JsonResponse(null, JsonResponse::HTTP_NOT_FOUND);
        }

        $paginatedCollection = $paginationFactory->createCollection($query, $request, 'products_list', [], 5);
        //\dd($paginatedCollection);

       
        /*return new JsonResponse(
            $this->serializer->serialize($paginatedCollection,'json',["groups" =>"products:list"]),
            JsonResponse::HTTP_OK, [], true
        );*/
        //return $this->respond($paginatedCollection,Response::HTTP_OK);
        /*$productsJson = $this->serializer->serialize(
            $paginatedCollection,
            'json',
            ["groups" => 'products:list']
        );*/
        //$context = SerializationContext::create()->setGroups(array("product:list"));
        $jsonData = $this->serializer->serialize($paginatedCollection, 'json', SerializationContext::create()->setGroups(['products:list']));
        return new JsonResponse($jsonData, JsonResponse::HTTP_OK, [], true);
        //\dd($productsJson);

        /*$response = new Response($productsJson, Response::HTTP_OK, ['Content-Type' => 'application/json']);

        return $response;*/
      

        /*return new JsonResponse(
            $this->serializer->serialize($this->productRepository->findAll(),"json", ["groups" => "products:list"]),
            JsonResponse::HTTP_OK, [], true
        );*/
    }

    /**
     * @Route("/{id}", name="product_details", methods={"GET"})
     * @IsGranted("ROLE_USER")
     * @OA\Get(summary="Get details of a product")
     * @OA\Response(
     *     response=JsonResponse::HTTP_OK,
     *     description="Returns a product"
     * )
     * @OA\Response(
     *     response=JsonResponse::HTTP_NOT_FOUND,
     *     description="Product not found"
     * )
     * @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Unique identifier of the product",
     *          required=true,
     *          example=4,
     *      )
     * @OA\Tag(name="Products")
     * @Cache(maxage="1 hour", public=true)
     * @param Product $product
     */
    public function details(Product $product=null, Request $request)
    {       
        $productId = $request->get('id');
        $product = $this->productRepository->findOneBy([
            'id' => $productId
        ]);
        if (!$product /*|| ($product instanceof Product)*/) {
            //throw new JsonException("Incorrect identifier or no product found with this identifier", JsonResponse::HTTP_NOT_FOUND);
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }
        /*return new JsonResponse(
            $this->serializer->serialize($product, "json", SerializationContext::create()->setGroups(
                ['product:details'])
            ),
            JsonResponse::HTTP_OK, [], true
        );*/
        $context = SerializationContext::create()->setGroups(array("product:details"));
        $jsonData = $this->serializer->serialize($product, 'json', $context);

        return new Response($jsonData, Response::HTTP_OK, [], true);
    }
}
