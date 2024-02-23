<?php

namespace App\Controller;

use App\Repository\CountryRepository;
use App\Repository\VatRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Exception\InvalidArgumentException;
use App\OptionsResolver\ProductOptionsResolver;
use App\Repository\ProductRepository;
use App\Entity\Product;
use App\Serializer\ProductSerializer;

#[Route("/api", name: "api_product_")]
class ProductController extends AbstractController
{
    private $productSerializer;

    public function __construct(ProductSerializer $productSerializer)
    {
        $this->productSerializer = $productSerializer;
    }

    #[Route('/products', name: 'index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): JsonResponse
    {
        $products = $productRepository->findAll();

        $formattedProducts = $this->productSerializer->serializeProducts($products);

        return $this->json($formattedProducts, 200, [], ['groups' => 'product']);
    }

    #[Route('/products/{id}', name: 'show', methods: ['GET'])]
    public function show(Product $product): JsonResponse
    {
        $response = $this->productSerializer->serializeProduct($product);

        return $this->json($response, 200, [], ['groups' => 'product']);
    }

    #[Route('/products', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, CountryRepository $countryRepository, VatRepository $vatRepository, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $product = new Product();

        $product->setPrice($data['price']);

        $product->setName($data['name']);

        if (isset($data['vat_ids']) && is_array($data['vat_ids'])) {
            foreach ($data['vat_ids'] as $vatId) {
                $vat = $vatRepository->findOneBy(['id' => $vatId]);
                if (!$vat) {
                    throw new InvalidArgumentException('Product with ID ' . $vatId . ' does not exist.');
                }
                $product->addVat($vat);
                $vat->addProduct($product);
                $entityManager->persist($vat);
            }
        }
        $product->setCreatedAt(new \DateTimeImmutable());
        $product->setUpdatedAt(new \DateTimeImmutable());
        $entityManager->persist($product);
        $entityManager->flush();
        $response = $this->productSerializer->serializeProduct($product);

        return $this->json($response, 200, [], ['groups' => 'product']);
    }

    #[Route("/products/{value}", "update", methods: ["PATCH", "PUT"])]
    public function update($value, Request $request, ProductOptionsResolver $productOptionsResolver, ValidatorInterface $validator, EntityManagerInterface $entityManager, ProductRepository $productRepository, VatRepository $vatRepository): JsonResponse
    {
        try {
            $product = $productRepository->find($value);

            if (!$product) {
                throw new NotFoundHttpException('Product not found');
            }

            $isPatchMethod = $request->getMethod() === "PUT";
            $requestBody = json_decode($request->getContent(), true);

            $fields = $productOptionsResolver
                ->configureName($isPatchMethod)
                ->configurePrice($isPatchMethod)
                ->configureVat($isPatchMethod)
                ->resolve($requestBody);

            foreach ($fields as $field => $value) {
                switch ($field) {
                    case "name":
                        $product->setName($value);
                        break;
                    case "price":
                        $product->setPrice($value);
                        break;
                    case "vat_ids":
                        $product->getVats()->clear();
                        if (isset($value) && is_array($value)) {
                            foreach ($value as $vatId) {
                                $vat = $vatRepository->findOneBy(['id' => $vatId]);
                                if (!$vat) {
                                    throw new InvalidArgumentException('Product with ID ' . $vatId . ' does not exist.');
                                }
                                $vat->addProduct($product);
                                $product->addVat($vat);
                                $entityManager->persist($vat);
                            }
                        }
                        break;
                }
            }

            $errors = $validator->validate($product);
            if (count($errors) > 0) {
                throw new InvalidArgumentException((string)$errors);
            }

            $entityManager->flush();
            $response = $this->productSerializer->serializeProduct($product);

            return $this->json($response, 200, [], ['groups' => 'product']);
        } catch (Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }

    #[Route('/products/{id}/add-vats', name: 'add_vats', methods: ['POST'])]
    public function addVatsToProduct(Product $product, Request $request, EntityManagerInterface $entityManager, VatRepository $vatRepository): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['vat_ids']) || !is_array($data['vat_ids'])) {
                throw new BadRequestHttpException('Invalid request format. Please provide an array of product_ids.');
            }

            foreach ($data['vat_ids'] as $vatId) {
                $vat = $vatRepository->findOneBy(['id' => $vatId]);

                if (!$vat) {
                    throw new BadRequestHttpException('Vat with ID ' . $vatId . ' does not exist.');
                }

                if (!$product->getVats()->contains($vat)) {
                    $product->addVat($vat);
                    $vat->addProduct($product);
                    $entityManager->persist($vat);
                }
            }

            $entityManager->flush();

            $response = $this->productSerializer->serializeProduct($product);

            return $this->json($response, 200, [], ['groups' => 'product']);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }

    #[Route('/products/{id}/delete-vats', name: 'vats', methods: ['POST'])]
    public function deleteVatsFromProduct(Product $product, Request $request, EntityManagerInterface $entityManager, VatRepository $vatRepository): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['vat_ids']) || !is_array($data['vat_ids'])) {
                throw new BadRequestHttpException('Invalid request format. Please provide an array of vat_ids.');
            }

            foreach ($data['vat_ids'] as $vatId) {
                $vat = $vatRepository->findOneBy(['id' => $vatId]);

                if (!$vat) {
                    throw new BadRequestHttpException('Product with ID ' . $vatId . ' does not exist.');
                }

                if ($product->getVats()->contains($vat)) {
                    $product->removeVat($vat);
                    $vat->removeProduct($product);
                    $entityManager->persist($vat);
                }
            }

            $entityManager->flush();

            $response = $this->productSerializer->serializeProduct($product);

            return $this->json($response, 200, [], ['groups' => 'product']);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }

    #[Route('/products/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Product $product, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($product);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
