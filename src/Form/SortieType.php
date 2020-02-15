<?php

namespace App\Form;


use App\Entity\Lieu;
use App\Entity\Site;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, ['label' => 'Nom de la sortie: ', 'attr'=>['autofocus' => true]])
            ->add('dateHeureDebut',DateTimeType::class, ['label' => 'Date et heure de la sortie : ', 'widget' => 'single_text'])
            ->add('duree', IntegerType::class, ['label' => 'Durée (minutes) : ', 'attr'=>['required'=> false]])
            ->add('dateLimiteInscription',DateTimeType::class, ['label' => "Date limite d'inscription : ", 'widget' => 'single_text'])
            ->add('nbInscriptionMax', IntegerType::class, ['label' => 'Nombre de places : '])
            ->add('infosSortie', TextareaType::class, ['label' => 'Description et infos : ', 'attr'=>['required'=> false]])

            ->add('lieuListe', EntityType::class, ['class'=>Lieu::class, 'choice_label'=>'nom',  'label'=> 'Lieu : ', 'mapped' => false, 'placeholder' => 'Choisir', 'empty_data'  => null, 'attr'=>['required'=> false]]) //,'attr' => ['empty_value' => 'vide!']
          //  ->add('lieu', LieuType::class, ['attr'=>['required'=> false]])
          //  ->add('site', TextType::class, ['class'=>Site::class, 'choice_label' => 'nom', 'label'=> 'Site : ', 'attr'=>['required'=> false]])
           // ->add('site', EntityType::class, ['class'=>Site::class, 'choice_label' => 'nom', 'label'=> 'Site : '])
            ->add('creer', SubmitType::class, ['label'=> 'Enregistrer', 'attr' => ['id'=> 'creer']])
            ->add('publier', SubmitType::class, ['label'=> 'Publier la sortie', 'attr' => ['id'=> 'publier']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
/*
 *
 * En tant qu'organisateur d'une sortie, je peux créer une nouvelle sortie ( définir un
nom pour la sortie, une date et heure, une durée, un lieu (nom, adresse, gps), un
nombre limite de participants, une note textuelle, et une date limite d'inscription )
 */