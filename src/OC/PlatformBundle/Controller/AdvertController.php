<?php
// src/OC/PlatformBundle/Controller/AdvertController.php

namespace OC\PlatformBundle\Controller;

use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Form\AdvertType;
use OC\PlatformBundle\Form\ApplyType;
use OC\PlatformBundle\Form\AdvertEditType;
use OC\PlatformBundle\Entity\AdvertSkill;
use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Entity\Categories;
use OC\PlatformBundle\Entity\Skill;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse; 
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use OC\PlatformBundle\Beta\BetaListener;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class AdvertController extends Controller
{
  public function indexAction($page)
  {
    if ($page < 1) {
      throw $this->createNotFoundException("La page ".$page." n'existe pas.");
    }

    // Ici je fixe le nombre d'annonces par page à 3
    // Mais bien sûr il faudrait utiliser un paramètre, et y accéder via $this->container->getParameter('nb_per_page')
    $nbPerPage = 3;

    // On récupère notre objet Paginator
    $listAdverts = $this->getDoctrine()
      ->getManager()
      ->getRepository('OCPlatformBundle:Advert')
      ->getAdverts($page, $nbPerPage)
    ;

    // On calcule le nombre total de pages grâce au count($listAdverts) qui retourne le nombre total d'annonces
    $nbPages = ceil(count($listAdverts) / $nbPerPage);

    // Si la page n'existe pas, on retourne une 404
    if ($page > $nbPages) {
      throw $this->createNotFoundException("La page ".$page." n'existe pas.");
    }
    // On instancie notre listener
    $BetaListener = new BetaListener('2018-12-20');

    // On récupère le gestionnaire d'évènements, qui heureusement est un service !
    $dispatcher = $this->get('event_dispatcher');

    // On dit au gestionnaire d'exécuter la méthode onKernelResponse de notre listener
    // lorsque l'évènement kernel.response est déclenché
    $dispatcher->addListener(
      'kernel.response',
      array($betaListener, 'processBeta')
    );
    // On donne toutes les informations nécessaires à la vue
    return $this->render('OCPlatformBundle:Advert:index.html.twig', array(
      'listAdverts' => $listAdverts,
      'nbPages'     => $nbPages,
      'page'        => $page,
    ));
  }

/**
 * @ParamConverter("advert", options={"mapping": {"advert_id": "id"}})
 */
public function viewAction(Advert $advert)
  {
    $em = $this->getDoctrine()->getManager();
    $id = $advert->getId();
    // On récupère l'annonce $id
    $advert = $em
      ->getRepository('OCPlatformBundle:Advert')
      ->find($id)
    ;

    if (null === $advert) {
      throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
    }

    // On avait déjà récupéré la liste des candidatures
    $listApplications = $em
      ->getRepository('OCPlatformBundle:Application')
      ->findBy(array('advert' => $advert))
    ;

    // On récupère maintenant la liste des AdvertSkill
    $listAdvertSkills = $em
      ->getRepository('OCPlatformBundle:AdvertSkill')
      ->findBy(array('advert' => $advert))
    ;

    return $this->render('OCPlatformBundle:Advert:view.html.twig', array(
      'advert'           => $advert,
      'listApplications' => $listApplications,
      'listAdvertSkills' => $listAdvertSkills
    ));
  }
  /**
   * @Security("has_role('ROLE_AUTEUR') and has_role('ROLE_USER')")
   */
public function addAction(Request $request)
  {
  
      // On vérifie que l'utilisateur dispose bien du rôle ROLE_AUTEUR
    if (!$this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
      // Sinon on déclenche une exception « Accès interdit »
      throw new AccessDeniedException('Accès limité aux auteurs.');
    }
    $advert = new Advert();
    $advert->setDate(new \Datetime());
    $form = $this->get('form.factory')->create(AdvertType::class, $advert);

    if ($request->isMethod('POST')) {
      $form->handleRequest($request);
      if ($form->isValid()) {
       $event = new MessagePostEvent($advert->getContent(), $advert->getUser());

        // On déclenche l'évènement
        $this->get('event_dispatcher')->dispatch(PlatformEvents::POST_MESSAGE, $event);
        $em = $this->getDoctrine()->getManager();
        $em->persist($advert);
        $em->flush();

        $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

        return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
      }
    }
    return $this->render('OCPlatformBundle:Advert:add.html.twig', array(
      'form' => $form->createView(),
    ));
  }
  public function getUser(){
  $user = new User('username', 'password');
  }
  public function addApplyAction(Request $request)
  {
    $em = $this->getDoctrine()->getManager();
    //$advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);
    $application = new Application();
    $application->setDate(new \Datetime());
    $form = $this->get('form.factory')->create(ApplyType::class, $application);

    if ($request->isMethod('POST')) {
      $form->handleRequest($request);
      if ($form->isValid()) {
        $em = $this->getDoctrine()->getManager();
        $em->persist($application);
        $em->flush();

        $request->getSession()->getFlashBag()->add('notice', 'bien enregistrée.');

       // return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
      }
    }
    return $this->render('OCPlatformBundle:Advert:addApply.html.twig', array(
      'formApply' => $form->createView(),
    ));
  }
   public function translationAction($name)
  {
    return $this->render('OCPlatformBundle:Advert:translation.html.twig', array(
      'name' => $name
    ));
  }
  public function editAction($id, Request $request)
  {
   
    $em = $this->getDoctrine()->getManager();

    // On récupère l'annonce $id
    $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

    if (null === $advert) {
      throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
    }
    $form = $this->get('form.factory')->create(AdvertEditType::class, $advert);
    if ($request->isMethod('POST')) {
       $form->handleRequest($request);
      if ($form->isValid()) {
        $em->persist($advert);
        $em->flush();

      $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');

      return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
    }
  }
    return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
      'advert' => $advert,
      'form' => $form->createView(),
    ));
  }
  public function editImageAction($advertId)
{
  $em = $this->getDoctrine()->getManager();

  // On récupère l'annonce
  $advert = $em->getRepository('OCPlatformBundle:Advert')->find($advertId);

  // On modifie l'URL de l'image par exemple
  $advert->getImage()->setUrl('test.png');

  // On n'a pas besoin de persister l'annonce ni l'image.
  // Rappelez-vous, ces entités sont automatiquement persistées car
  // on les a récupérées depuis Doctrine lui-même
  
  // On déclenche la modification
  $em->flush();

  return new Response('OK');
}

  public function deleteAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();

    // On récupère l'annonce $id
    $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

    if (null === $advert) {
      throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
    }

    $form = $this->get('form.factory')->create();

    if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
      $em->remove($advert);
      $em->flush();
      $request->getSession()->getFlashBag()->add('info', "L'annonce a bien été supprimée.");

      return $this->redirectToRoute('oc_platform_home');
    }

        return $this->render('OCPlatformBundle:Advert:delete.html.twig', array(
      'advert' => $advert,
      'form'   => $form->createView(),
    ));
  }
  public function menuAction($limit)
  {
    // On fixe en dur une liste ici, bien entendu par la suite
    // on la récupérera depuis la BDD !
    $em = $this->getDoctrine()->getManager();
    $listAdverts =  $em->getRepository('OCPlatformBundle:Advert')->findBy(
      array(),                 // Pas de critère
      array('date' => 'desc'), // On trie par date décroissante
      $limit,                  // On sélectionne $limit annonces
      0                        // À partir du premier
    );

    return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
      'listAdverts' => $listAdverts
    ));
  }
  public function testAction(Advert $advert)
  {
    $advert = new Advert();
    $advert->setTitle("Développeur Full-stack H/F");

    $em = $this->getDoctrine()->getManager();
    $em->persist($advert);
    $em->flush(); // C'est à ce moment qu'est généré le slug

    return new Response('Slug généré : '.$advert->getSlug());
    // Affiche « Slug généré : recherche-developpeur »
  }
    public function purgeAction($days, Request $request)
  {
    // On récupère le service de purge
    $purgerAdvert = $this->get('oc_platform.purger.advert');
    // On lance la purge
    $purgerAdvert->purge($days);
    // MEssage flash
    $request->getSession()->getFlashBag()->add('info', 'Annonces purgées');
    // Redirection vers l'accueil général
    return $this->redirectToRoute('oc_core_home');
  }
  /**
 * @ParamConverter("date", options={"format": "Y-m-d"})
 */
  public function viewListAction(\Datetime $date)
  {

  }
    /**
   * @ParamConverter("json")
   */
  public function ParamConverterAction($json)
  {
    return new Response(print_r($json, true));
  }
}