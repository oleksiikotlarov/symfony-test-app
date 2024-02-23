<?php


namespace App\OptionsResolver;

use Symfony\Component\OptionsResolver\OptionsResolver;

class VatOptionsResolver extends OptionsResolver
{
    public function configureRate(bool $isRequired = true): self
    {
        $this->setDefined("rate")->setAllowedTypes("rate", "int");

        if ($isRequired) {
            $this->setRequired("rate");
        }

        return $this;
    }

    public function configureCountry(bool $isRequired = true): self
    {
        $this->setDefined("country_id")->setAllowedTypes("country_id", "int");

        if ($isRequired) {
            $this->setRequired("country_id");
        }

        return $this;
    }

    public function configureProduct(bool $isRequired = true): self
    {
        $this->setDefined("product_ids")->setAllowedTypes("product_ids", "array");

        if ($isRequired) {
            $this->setRequired("product_ids");
        }

        return $this;
    }

}