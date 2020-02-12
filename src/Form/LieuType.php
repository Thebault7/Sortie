<?php

namespace App\Form;

use App\Entity\Lieu;

use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, ['label' => ' Nom du lieu : ', 'attr'=>['required'=> false]])
            ->add('rue', TextType::class, ['label' => ' Rue : ', 'attr'=>['required'=> false]])
            //->add('latitude')
           // ->add('longitude')
            ->add('villeListe', EntityType::class, ['class'=>Ville::class, 'choice_label'=>'nom', 'label'=> 'Ville : ', 'mapped' => false, 'placeholder' => 'Choisir', 'empty_data'  => null, 'attr'=> ['required'=>false]])
            ->add('ville', VilleType::class, ['attr'=>['required'=> false]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}
