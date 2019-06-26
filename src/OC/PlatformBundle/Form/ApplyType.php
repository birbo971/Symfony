<?php

namespace OC\PlatformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use OC\PlatformBundle\Repository\CategoryRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ApplyType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    // Arbitrairement, on récupère toutes les catégories qui commencent par "D"
    //$pattern = '';
    $builder
      ->add('date',      DateType::class)
      ->add('author',    TextType::class)
      ->add('content',   TextareaType::class)
      ->add('email',     EmailType::class)
      ->add('save',      SubmitType::class);
       // On ajoute une fonction qui va écouter un évènement
    $builder->addEventListener(
      FormEvents::PRE_SET_DATA,    // 1er argument : L'évènement qui nous intéresse : ici, PRE_SET_DATA
      function(FormEvent $event) { // 2e argument : La fonction à exécuter lorsque l'évènement est déclenché
        // On récupère notre objet Advert sous-jacent
        $application = $event->getData();

        // Cette condition est importante, on en reparle plus loin
        if (null === $application) {
          return; // On sort de la fonction sans rien faire lorsque $advert vaut null
        }
      }
    );
  }

  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults(array(
      'data_class' => 'OC\PlatformBundle\Entity\Application'
    ));
  }

}
