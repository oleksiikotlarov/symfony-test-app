<?php

namespace App\Controller;

use App\Repository\CountryRepository;
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
use App\OptionsResolver\VatOptionsResolver;
use App\Repository\VatRepository;
use App\Repository\ProductRepository;
use App\Entity\Vat;
use App\Serializer\VatSerializer;
use Symfony\Component\Validator\Constraints as Assert;

#[Route("/api", name: "api_vat_")]
class VatController extends AbstractController
{
    private $vatSerializer;

    public function __construct(VatSerializer $vatSerializer)
    {
        $this->vatSerializer = $vatSerializer;
    }

    #[Route('/vats', name: 'index', methods: ['GET'])]
    public function index(VatRepository $vatRepository): JsonResponse
    {
        $vats = $vatRepository->findAll();

        $formattedVats = $this->vatSerializer->serializeVats($vats);

        return $this->json($formattedVats, 200, [], ['groups' => 'vat']);
    }

    #[Route('/vats/{id}', name: 'show', methods: ['GET'])]
    public function show(Vat $vat): JsonResponse
    {
        $response = $this->vatSerializer->serializeVat($vat);

        return $this->json($response, 200, [], ['groups' => 'vat']);
    }

    #[Route('/vats', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, CountryRepository $countryRepository, ProductRepository $productRepository, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $vat = new Vat();
        $errors = $validator->validate($data['rate'], [
            new Assert\NotBlank(),
            new Assert\Range(['min' => 0, 'max' => 20]),
        ]);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }

            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $vat->setRate($data['rate']);

        if (isset($data['country_id'])) {
            $country = $countryRepository->findOneBy(['id' => $data['country_id']]);
            if (!$country) {
                throw new InvalidArgumentException('Country you provided in non existent');
            }
            $vat->setCountry($country);
        }
        if (isset($data['product_ids']) && is_array($data['product_ids'])) {
            foreach ($data['product_ids'] as $productId) {
                $product = $productRepository->findOneBy(['id' => $productId]);
                if (!$product) {
                    throw new InvalidArgumentException('Product with ID ' . $productId . ' does not exist.');
                }
                $vat->addProduct($product);
                $product->addVat($vat);
                $entityManager->persist($product);
            }
        }
        $vat->setCreatedAt(new \DateTimeImmutable());
        $vat->setUpdatedAt(new \DateTimeImmutable());
        $entityManager->persist($vat);
        $entityManager->flush();

        $response = $this->vatSerializer->serializeVat($vat);

        return $this->json($response, 200, [], ['groups' => 'vat']);
    }

    #[Route("/vats/{value}", "update", methods: ["PATCH", "PUT"])]
    public function update($value, Request $request, VatOptionsResolver $vatOptionsResolver, ValidatorInterface $validator, EntityManagerInterface $entityManager, CountryRepository $countryRepository, ProductRepository $productRepository, VatRepository $vatRepository): JsonResponse
    {
        try {
            $vat = $vatRepository->find($value);

            if (!$vat) {
                throw new NotFoundHttpException('Vat not found');
            }

            $isPatchMethod = $request->getMethod() === "PUT";
            $requestBody = json_decode($request->getContent(), true);

            $fields = $vatOptionsResolver
                ->configureRate($isPatchMethod)
                ->configureCountry($isPatchMethod)
                ->configureProduct($isPatchMethod)
                ->resolve($requestBody);

            foreach ($fields as $field => $value) {
                switch ($field) {
                    case "rate":
                        $errors = $validator->validate($value, [
                            new Assert\NotBlank(),
                            new Assert\Range(['min' => 0, 'max' => 20]),
                        ]);

                        if (count($errors) > 0) {
                            $errorMessages = [];
                            foreach ($errors as $error) {
                                $errorMessages[] = $error->getMessage();
                            }

                            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
                        }

                        $vat->setRate($value);
                        break;
                    case "country_id":
                        $country = $countryRepository->findOneBy(['id' => $value]);

                        if (!$country) {
                            throw new NotFoundHttpException('Country not found');
                        }

                        $vat->setCountry($country);
                        break;
                    case "product_ids":
                        $vat->getProducts()->clear();
                        if (isset($value) && is_array($value)) {
                            foreach ($value as $productId) {
                                $product = $productRepository->findOneBy(['id' => $productId]);
                                if (!$product) {
                                    throw new InvalidArgumentException('Product with ID ' . $productId . ' does not exist.');
                                }
                                $vat->addProduct($product);
                                $product->addVat($vat);
                                $entityManager->persist($product);
                            }
                        }
                        break;
                }
            }

            $errors = $validator->validate($vat);
            if (count($errors) > 0) {
                throw new InvalidArgumentException((string)$errors);
            }

            $entityManager->flush();
            $response = $this->vatSerializer->serializeVat($vat);

            return $this->json($response, 200, [], ['groups' => 'vat']);
        } catch (Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }

    #[Route('/vats/{id}/add-products', name: 'add_products', methods: ['POST'])]
    public function addProductsToVat(Vat $vat, Request $request, EntityManagerInterface $entityManager, ProductRepository $productRepository): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['product_ids']) || !is_array($data['product_ids'])) {
                throw new BadRequestHttpException('Invalid request format. Please provide an array of product_ids.');
            }

            foreach ($data['product_ids'] as $productId) {
                $product = $productRepository->findOneBy(['id' => $productId]);

                if (!$product) {
                    throw new BadRequestHttpException('Product with ID ' . $productId . ' does not exist.');
                }

                if (!$vat->getProducts()->contains($product)) {
                    $vat->addProduct($product);
                    $product->addVat($vat);
                    $entityManager->persist($product);
                }
            }

            $entityManager->flush();

            $response = $this->vatSerializer->serializeVat($vat);

            return $this->json($response, 200, [], ['groups' => 'vat']);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }

    #[Route('/vats/{id}/delete-products', name: 'delete_products', methods: ['POST'])]
    public function deleteProductsFromVat(Vat $vat, Request $request, EntityManagerInterface $entityManager, ProductRepository $productRepository): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['product_ids']) || !is_array($data['product_ids'])) {
                throw new BadRequestHttpException('Invalid request format. Please provide an array of product_ids.');
            }

            foreach ($data['product_ids'] as $productId) {
                $product = $productRepository->findOneBy(['id' => $productId]);

                if (!$product) {
                    throw new BadRequestHttpException('Product with ID ' . $productId . ' does not exist.');
                }

                if ($vat->getProducts()->contains($product)) {
                    $vat->removeProduct($product);
                    $product->removeVat($vat);
                    $entityManager->persist($product);
                }
            }

            $entityManager->flush();

            $response = $this->vatSerializer->serializeVat($vat);

            return $this->json($response, 200, [], ['groups' => 'vat']);
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }

    #[Route('/vats/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Vat $vat, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($vat);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
