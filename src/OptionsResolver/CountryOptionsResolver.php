<?php


namespace App\OptionsResolver;

use Symfony\Component\OptionsResolver\OptionsResolver;

class CountryOptionsResolver extends OptionsResolver
{
    public function configureName(bool $isRequired = true): self
    {
        $this->setDefined("name")->setAllowedTypes("name", "string");

        if ($isRequired) {
            $this->setRequired("name");
        }

        return $this;
    }

    public function configureLocale(bool $isRequired = true): self
    {
        $this->setDefined("locale")->setAllowedTypes("locale", "string");

        if ($isRequired) {
            $this->setRequired("locale");
        }

        return $this;
    }

}