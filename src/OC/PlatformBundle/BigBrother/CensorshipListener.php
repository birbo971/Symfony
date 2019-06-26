<?php
// src/OC/PlatformBundle/Bigbrother/CensorshipListener.php

namespace OC\PlatformBundle\Bigbrother;

use OC\PlatformBundle\Event\PlatformEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MessageListener implements EventSubscriberInterface
{
  // La méthode de l'interface que l'on doit implémenter, à définir en static
  static public function getSubscribedEvents()
  {
    // On retourne un tableau « nom de l'évènement » => « méthode à exécuter »
    return array(
      PlatformEvents::POST_MESSAGE    => array('processMessage' => 2),
      PlatformEvents::AUTRE_EVENEMENT => array('autreMethode' => 0)
      // ...
    );
  }

  public function processMessage(MessagePostEvent $event)
  {
    // ...
  }

  public function autreMethode()
  {
    // ...
  }
}