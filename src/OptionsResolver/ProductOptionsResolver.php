<?php


namespace App\OptionsResolver;

use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductOptionsResolver extends OptionsResolver
{
    public function configurePrice(bool $isRequired = true): self
    {
        $this->setDefined("price")->setAllowedTypes("price", "int");

        if ($isRequired) {
            $this->setRequired("price");
        }

        return $this;
    }

    public function configureName(bool $isRequired = true): self
    {
        $this->setDefined("name")->setAllowedTypes("name", "string");

        if ($isRequired) {
            $this->setRequired("name");
        }

        return $this;
    }

    public function configureVat(bool $isRequired = true): self
    {
        $this->setDefined("vat_ids")->setAllowedTypes("vat_ids", "array");

        if ($isRequired) {
            $this->setRequired("vat_ids");
        }

        return $this;
    }

}