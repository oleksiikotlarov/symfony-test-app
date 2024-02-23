<?php

namespace App\OptionsResolver;

use Symfony\Component\OptionsResolver\OptionsResolver;

class LocaleOptionsResolver extends OptionsResolver
{
    public function configureName(bool $isRequired = true): self
    {
        $this->setDefined("name")->setAllowedTypes("name", "string");

        if($isRequired) {
            $this->setRequired("name");
        }

        return $this;
    }

    public function configureIso(bool $isRequired = true): self
    {
        $this->setDefined("iso_code")->setAllowedTypes("iso_code", "string");

        if($isRequired) {
            $this->setRequired("iso_code");
        }

        return $this;
    }
}