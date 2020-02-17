<?php

namespace App\Form;

use App\Entity\Ville;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VilleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('nom', TextType::class, ['label' => ' ', 'attr'=>['required'=> false]])
            ->add('codePostal', TextType::class, ['label' => ' ', 'attr'=>['required'=> false]])
        ;
    }



    public function findByCodePostalEtNom($codePostal, $nom){

        return $this->createQueryBuilder('v')
            ->andWhere('v.codePostal = :val1', 'v.nom = :val2')
            ->setParameter('val1', $codePostal)
            ->setParameter('val2', $nom)
            ->getQuery()
            ->getResult();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ville::class,
        ]);
    }
}
