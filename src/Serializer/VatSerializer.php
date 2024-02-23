<?php

namespace App\Serializer;

use Symfony\Component\Serializer\SerializerInterface;
use App\Entity\Vat;

class VatSerializer
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function serializeVat(Vat $vat): array
    {
        $vatData = [
            'id' => $vat->getId(),
            'rate' => $vat->getRate(),
            'createdAt' => $vat->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $vat->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
        $country = $vat->getCountry();
        if ($country !== null) {
            $vatData['country'] = [
                'id' => $country->getId(),
                'name' => $country->getName(),
                'locale' => $country->getLocale()
            ];
        }

        $products = $vat->getProducts();
        if ($products->count() > 0) {
            $vatData['products'] = $this->serializer->normalize($products, null, ['groups' => 'vat']);
        }


        return $vatData;
    }

    public function serializeVats(array $vats): array
    {
        $formattedVats = [];
        foreach ($vats as $vat) {
            $formattedVats[] = $this->serializeVat($vat);
        }

        return $formattedVats;
    }
}
