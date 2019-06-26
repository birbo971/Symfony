<?php

// src/OC/PlatformBundle/Controller/AdvertCCoreontroller.php

namespace OC\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse; // N'oubliez pas ce use
use Symfony\Component\HttpFoundation\JsonResponse;



class AdvertCoreController extends Controller
{
    public function indexAction()
    {
    $em = $this->getDoctrine()->getManager();
    $listAdverts = $em->getRepository('OCPlatformBundle:Advert')->findAll();

    // Et modifiez le 2nd argument pour injecter notre liste
    return $this->render('OCCoreBundle:AdvertCore:index.html.twig', array(
      'listAdverts' => $listAdverts
    ));
        // On a donc accès au conteneur :
    $mailer = $this->container->get('mailer'); 

    // On peut envoyer des e-mails, etc.
  }
   public function contactAction()
    {
      $this->get('session')->getFlashBag('notice', 'La page de contact n’est pas encore disponible, merci de revenir plus tard.');
      // Si on n'est pas en POST, alors on affiche le formulaire
      return $this->render('OCCoreBundle:AdvertCore:contact.html.twig');
    }
    public function getUserManager(){
          // Pour récupérer le service UserManager du bundle
      $userManager = $this->get('fos_user.user_manager');

      // Pour charger un utilisateur
      $user = $userManager->findUserBy(array('username' => 'bboi'));

      // Pour modifier un utilisateur
      $user->setEmail('cetemail@nexiste.pas');
      $userManager->updateUser($user); // Pas besoin de faire un flush avec l'EntityManager, cette méthode le fait toute seule !

      // Pour supprimer un utilisateur
      $userManager->deleteUser($user);

      // Pour récupérer la liste de tous les utilisateurs
      $users = $userManager->findUsers();
    }
}