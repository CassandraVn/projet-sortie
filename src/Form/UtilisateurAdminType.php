<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UtilisateurAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo', TextType::class, ['label'=>'Pseudo'])
            ->add('plainPassword', RepeatedType::class, [
                'type'=>PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent être identiques.',
                'required'=>false,
                'mapped'=> false,
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmation']
            ])
            ->add('nom', TextType::class, ['label'=>'Nom'])
            ->add('prenom', TextType::class, ['label'=>'Prénom'])
            ->add('telephone', TelType::class, ['label'=>'Téléphone'])
            ->add('mail', EmailType::class, ['label'=>'Email'])
            ->add('campus', EntityType::class, [
                'label'=>'Campus',
                'class' => Campus::class,
                'choice_label' => 'nom'
            ])
            ->add('photo', FileType::class,
                ['label'=>'Ma photo',
                    'required'=>false,
                    'mapped'=> false,
                    'constraints'=>[
                        new File([
                            'mimeTypes' => ['image/jpeg', 'image/png'],
                            'mimeTypesMessage' => 'Veuillez choisir une photo en format JPEG ou PNG.'
                        ])
                    ]])
            ->add('roles', ChoiceType::class,[
                'choices'  => [
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
                'multiple'=>true
            ])
            ->add('ajout', SubmitType::class, [
                'label'=>'Ajouter',
                'attr' => ['class' => 'btn btn-outline-primary btn-submit btn-form']

            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
