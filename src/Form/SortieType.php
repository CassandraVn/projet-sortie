<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Repository\CampusRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use function PHPUnit\Framework\logicalAnd;

class SortieType extends AbstractType
{

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security=$security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, ['label'=>"Nom sortie"])
            ->add('dateHeureDebut', DateTimeType::class, ['label'=>"Date de sortie"])
            ->add('duree', IntegerType::class, ['label'=>"Duree"])
            ->add('dateLimiteInscription' , DateTimeType::class, ['label'=>"Date limite d'inscription"])
            ->add('nbInscriptionMax', IntegerType::class, ['label'=>"Nombre maximum de participants"])
            ->add('infosSortie', TextareaType::class, ['label' => "Description"])
            ->add('lieu' , EntityType::class, [
                'label' => 'Lieu',
                'class' => Lieu::class,
                'choice_label' => 'nom'
             ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'label' => 'Campus',
                'choice_label' => 'nom',
                'data' => $this->security->getUser()->getCampus()
                ])
            ->add('motifAnnulation' , TextareaType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
            'required' => false
        ]);
    }
}
