<?php

namespace App\Controller;

use App\Entity\Product;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Attributes as OA;

class ProductController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer,
        private TagAwareCacheInterface $cachePool
    ) {
    }

    /**
     * Cette méthode permet de récupérer tous les produits que BileMo propose.
     */
    #[Route('/api/product', name: 'all_products', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des produits',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Product::class))
        )
    )]
    #[OA\Parameter(
        name: 'page',
        in: 'query',
        description: 'Choisissez la page souhaité',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'limit',
        in: 'query',
        description: 'Le nombre de produits affiché',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'Product')]
    public function getProductsPagination(
        Request $request,
    ): JsonResponse {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        $idCache = 'getAllProducts-' . $page . '-' . $limit;
        $products = $this->cachePool->get(
            $idCache,
            function (ItemInterface $item) use ($page, $limit) {
                $item->tag('productCache');

                return $this->em->getRepository(Product::class)->findAllProductPagination($page, $limit);
            }
        );
        $jsonProducts = $this->serializer->serialize($products, 'json');

        return new JsonResponse($jsonProducts, Response::HTTP_OK, [], true);
    }

    /**
     * Cette méthode permet de récupérer tous les produits que BileMo propose.
     */
    #[Route('/api/product/{id}', name: 'one_product', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste d\'un produit',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Product::class))
        )
    )]
    #[OA\Tag(name: 'Product')]
    public function getProduct(Product $product): JsonResponse
    {
        $idCache = 'getProduct-' . $product->getId();
        $productData = $this->cachePool->get(
            $idCache,
            function (ItemInterface $item) use ($product) {
                $item->tag('productCache');

                return $this->em->getRepository(Product::class)->findOneBy(['id' => $product->getId()]);
            }
        );
        $jsonProduct = $this->serializer->serialize($productData, 'json');
        return new JsonResponse($jsonProduct, Response::HTTP_OK, ['accept' => 'json'], true);
    }
}
