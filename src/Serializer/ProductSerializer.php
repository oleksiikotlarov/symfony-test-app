<?php

namespace App\Serializer;

use Symfony\Component\Serializer\SerializerInterface;
use App\Entity\Product;

class ProductSerializer
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function serializeProduct(Product $product): array
    {
        $productData = [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'price' => $product->getPrice(),
            'createdAt' => $product->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $product->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];

        $vats = $product->getVats();
        if ($vats->count() > 0) {
            $productData['vats'] = $this->serializer->normalize($vats, null, ['groups' => 'product']);
        }

        return $productData;
    }

    public function serializeProducts(array $products): array
    {
        $formattedProducts = [];
        foreach ($products as $product) {
            $formattedProducts[] = $this->serializeProduct($product);
        }

        return $formattedProducts;
    }
}
