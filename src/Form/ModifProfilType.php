<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ModifProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pseudo',  TextType::class, ['label' => 'Pseudo :'])
            ->add('nom', TextType::class, ['label' => 'Nom :'])
            ->add('prenom',  TextType::class, ['label' => 'Prénom :'])
            ->add('telephone',  TextType::class, ['label' => 'Téléphone :'])
            ->add('mail',  EmailType::class, ['label' => 'Email :'])
            ->add('password',   RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password']])

            ->add('site',  EntityType::class, ['class' => Site::class, 'choice_label' => 'nom', 'label' => 'Nom du site : '])
            ->add('photo', FileType::class, [
                'label' => 'Ma photo : ',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // everytime you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                            'image/jpg'
                        ],
                        'mimeTypesMessage' => 'Ajoutez une image au format .jpg/.jpeg/.png',
                    ])
                ],
            ])
            ->add('save',    SubmitType::class, ['label' => 'Enregistrer :'])
            ->add('cancel',    SubmitType::class, ['label' => 'Annuler :'])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
