<?php
// src/OC/PlatformBundle/Validator/Antiflood.php

namespace OC\PlatformBundle\Validators;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Antiflood(message="Mon message personnalisé")
 */
class Antiflood extends Constraint
{
  public $message = "Vous avez déjà posté un message il y a moins de 15 secondes, merci d'attendre un peu.";

  public function validatedBy()
  {
    return 'oc_platform_antiflood'; // Ici, on fait appel à l'alias du service
  }
}