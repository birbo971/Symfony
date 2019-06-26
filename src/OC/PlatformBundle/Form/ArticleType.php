<?php
// src/OC/PlatformBundle/Form/ArticleType.php

namespace OC\PlatformBundle\Form;

class AdvertType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
      // …
      ->add('content', CkeditorType::class)
    ;
  }

  // …
}