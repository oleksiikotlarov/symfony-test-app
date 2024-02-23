<?php

namespace App\Serializer;

use Symfony\Component\Serializer\SerializerInterface;
use App\Entity\Country;

class CountrySerializer
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function serializeCountry(Country $country): array
    {
        $countryData = [
            'id' => $country->getId(),
            'name' => $country->getName(),
            'createdAt' => $country->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $country->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];

        $locale = $country->getLocale();
        if ($locale !== null) {
            $countryData['locale'] = [
                'id' => $locale->getId(),
                'name' => $locale->getName(),
                'iso_code' => $locale->getIsoCode(),
            ];
        }

        return $countryData;
    }

    public function serializeCountries(array $countries): array
    {
        $formattedCountries = [];
        foreach ($countries as $country) {
            $formattedCountries[] = $this->serializeCountry($country);
        }

        return $formattedCountries;
    }
}
