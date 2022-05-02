<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/products")
 */
class ProductController extends AbstractController
{
    /**
     * @Route(name="api_products_list", methods={"GET"})
     * @return JsonResponse
     */
    public function list(ProductRepository $productRepository, SerializerInterface $serializer): JsonResponse
    {
        return new JsonResponse(
            $serializer->serialize($productRepository->findAll(),"json", ["groups" => "get"]),
            JsonResponse::HTTP_OK, [], true
        );
    }

    /**
     * @Route("/{id}", name="api_products_details", methods={"GET"})
     * @return JsonResponse
     * @param Product $product
     */
    public function details(Product $product, SerializerInterface $serializer): JsonResponse
    {   
        return new JsonResponse(
            $serializer->serialize($product, "json", ["groups" => "get"]),
            JsonResponse::HTTP_OK, [], true
        );
    }
}
