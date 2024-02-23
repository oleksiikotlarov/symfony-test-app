<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LocaleRepository;
use App\OptionsResolver\LocaleOptionsResolver;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use App\Entity\Locale;

#[Route("/api", name: "api_locale_")]
class LocaleController extends AbstractController
{
    #[Route('/locales', name: 'index', methods: ['GET'])]
    public function index(LocaleRepository $localeRepository): JsonResponse
    {
        $locales = $localeRepository->findAll();

        return $this->json($locales, 200, [], ['groups' => 'locale']);
    }

    #[Route('/locales/{value}', name: 'show', methods: ['GET'])]
    public function show($value, LocaleRepository $localeRepository): JsonResponse
    {
        $locale = $localeRepository->findByIdOrIsoCode($value);

        if (!$locale) {
            throw new NotFoundHttpException('Locale not found');
        }

        return $this->json($locale, 200, [], ['groups' => 'locale']);
    }

    #[Route('/locales', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $locale = new Locale();
        $locale->setName($data['name']);
        $locale->setIsoCode($data['iso_code']);
        $locale->setCreatedAt(new \DateTimeImmutable());
        $locale->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->persist($locale);
        $entityManager->flush();

        return $this->json($locale, 200, [], ['groups' => 'locale']);
    }

    #[Route("/locales/{value}", "update", methods: ["PATCH", "PUT"])]
    public function update($value, Request $request, LocaleOptionsResolver $localeOptionsResolver, ValidatorInterface $validator, EntityManagerInterface $entityManager, LocaleRepository $localeRepository ): JsonResponse
    {
        try {
            $locale = $localeRepository->findByIdOrIsoCode($value);

            if (!$locale) {
                throw new NotFoundHttpException('Locale not found');
            }

            $isPatchMethod = $request->getMethod() === "PUT";
            $requestBody = json_decode($request->getContent(), true);

            $fields = $localeOptionsResolver
                ->configureName($isPatchMethod)
                ->configureIso($isPatchMethod)
                ->resolve($requestBody);

            foreach($fields as $field => $value) {
                switch($field) {
                    case "name":
                        $locale->setName($value);
                        break;
                    case "iso_code":
                        $locale->setIsoCode($value);
                        break;
                }
            }

            $errors = $validator->validate($locale);
            if (count($errors) > 0) {
                throw new InvalidArgumentException((string) $errors);
            }

            $entityManager->flush();

            return $this->json($locale, 200, [], ['groups' => 'locale']);
        } catch(Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }

    #[Route('/locales/{value}', name: 'delete', methods: ['DELETE'])]
    public function delete($value, EntityManagerInterface $entityManager, LocaleRepository $localeRepository): JsonResponse
    {
        $locale = $localeRepository->findByIdOrIsoCode($value);

        if (!$locale) {
            throw $this->createNotFoundException('Locale not found');
        }

        $entityManager->remove($locale);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
