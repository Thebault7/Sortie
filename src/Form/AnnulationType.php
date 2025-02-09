<?php

namespace App\Form;

use App\Entity\Annulation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnulationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('motif', TextareaType::class, ['label' => 'Motif : ', 'attr'=>['required'=> true]])
           // ->add('sortie')
           ->add('save', SubmitType::class, ['label'=> 'Enregistrer', 'attr' => ['id'=> 'enregistrer']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Annulation::class,
        ]);
    }
}
