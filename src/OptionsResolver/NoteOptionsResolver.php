<?php

namespace App\OptionsResolver;

use Symfony\Component\OptionsResolver\OptionsResolver;

class NoteOptionsResolver extends OptionsResolver
{
    public function configureContent(bool $isRequired = true): self
    {
      $this->setDefined("content")->setAllowedTypes("content", "string");
  
      if($isRequired) {
        $this->setRequired("content");
      }
  
      return $this;
    }
}