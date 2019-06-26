<?php
namespace OC\PlatformBundle\Purger;
use Doctrine\ORM\EntityManagerInterface;

class PurgerAdvert
{
  function purge($days)
  {
    //Récupère les repositories
    $em = $this->getDoctrine()->getManager();
    $advertRepository = $this->em->getRepository('OCPlatformBundle:Advert');
    $advertSkillRepository = $this->em->getRepository('OCPlatformBundle:AdvertSkill');
    // Récupère la liste des annonces à purger
    $listAdverts = $advertRepository->getAdvertsToPurge(new \Datetime('-'.$days.' day'));
    // Supprime les annonces
    foreach ($listAdverts as $advert)
    {
      // On n'oublie pas de supprimer d'abord chaque AdvertSkill lié à l'annonce
      $advertSkills = $advertSkillRepository->findByAdvert($advert);
      foreach ($advertSkills as $advertSkill)
      {
        $this->em->remove($advertSkill);
      }
      // Au final on supprime l'annonce elle-même
      $this->em->remove($advert);
    }
    // a la fin on flush le tout
    $this->em->flush();
  }
} 

?>