<?php

namespace App\Form;

use App\Entity\Sortie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, ['label' => 'Nom de la sortie: '])
            ->add('dateHeureDebut',DateTimeType::class, ['label' => 'Date et heure de la sortie : '])
            ->add('duree', IntegerType::class, ['label' => 'Durée : '])
            ->add('dateLimiteInscription',DateTimeType::class, ['label' => "Date limite d'inscription : "])
            ->add('nbInscriptionMax', IntegerType::class, ['label' => 'Nombre de places : '])
            ->add('infosSortie', TextType::class, ['label' => 'Description et infos : '])
          //  ->add('etat')
            ->add('lieu')
            ->add('site')
            ->add('participant')
            ->add('participants')
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