<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    #[Route('/api/products', name: 'get_products', methods: ['GET'])]
    public function getProducts(ProductRepository $repository): JsonResponse {
        $products = $repository->findAll();
        return $this->json($products);
    }
}
