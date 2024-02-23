<?php

namespace App\Controller;

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
use App\OptionsResolver\CountryOptionsResolver;
use App\Repository\LocaleRepository;
use App\Repository\CountryRepository;
use App\Entity\Country;
use App\Serializer\CountrySerializer;

#[Route("/api", name: "api_country_")]
class CountryController extends AbstractController
{
    private $countrySerializer;

    public function __construct(CountrySerializer $countrySerializer)
    {
        $this->countrySerializer = $countrySerializer;
    }

    #[Route('/countries', name: 'index', methods: ['GET'])]
    public function index(CountryRepository $countryRepository): JsonResponse
    {
        $countries = $countryRepository->findAll();

        $formattedCountries = $this->countrySerializer->serializeCountries($countries);

        return $this->json($formattedCountries, 200, [], ['groups' => 'country']);
    }

    #[Route('/countries/{id}', name: 'show', methods: ['GET'])]
    public function show(Country $country): JsonResponse
    {
        $countryData = $this->countrySerializer->serializeCountry($country);

        return $this->json($countryData, 200, [], ['groups' => 'country']);
    }

    #[Route('/countries', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, LocaleRepository $localeRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $country = new Country();
        $country->setName($data['name']);
        if (isset($data['locale'])) {
            $locale = $localeRepository->findOneBy(['iso_code' => $data['locale']]);
            if (!$locale) {
                throw new InvalidArgumentException('Locale you provided in non existent');
            }
            $country->setLocale($locale);
        }
        $country->setCreatedAt(new \DateTimeImmutable());
        $country->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->persist($country);
        $entityManager->flush();

        return $this->json($country, 200, [], ['groups' => 'country']);
    }

    #[Route("/countries/{value}", "update", methods: ["PATCH", "PUT"])]
    public function update($value, Request $request, CountryOptionsResolver $countryOptionsResolver, ValidatorInterface $validator, EntityManagerInterface $entityManager, CountryRepository $countryRepository, LocaleRepository $localeRepository): JsonResponse
    {
        try {
            $country = $countryRepository->find($value);

            if (!$country) {
                throw new NotFoundHttpException('Country not found');
            }

            $isPatchMethod = $request->getMethod() === "PUT";
            $requestBody = json_decode($request->getContent(), true);

            $fields = $countryOptionsResolver
                ->configureName($isPatchMethod)
                ->configureLocale($isPatchMethod)
                ->resolve($requestBody);

            foreach ($fields as $field => $value) {
                switch ($field) {
                    case "name":
                        $country->setName($value);
                        break;
                    case "locale":
                        $locale = $localeRepository->findOneBy(['iso_code' => $value]);

                        if (!$locale) {
                            throw new NotFoundHttpException('Locale not found');
                        }

                        $country->setLocale($locale);
                        break;
                }
            }

            $errors = $validator->validate($country);
            if (count($errors) > 0) {
                throw new InvalidArgumentException((string)$errors);
            }

            $entityManager->flush();

            $response = $this->countrySerializer->serializeCountry($country);

            return $this->json($response, 200, [], ['groups' => 'country']);
        } catch (Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }

    #[Route('/countries/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Country $country, EntityManagerInterface $entityManager, CountryRepository $countryRepository): JsonResponse
    {
        $entityManager->remove($country);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
