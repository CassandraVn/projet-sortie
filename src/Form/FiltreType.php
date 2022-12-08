<?php

namespace App\Form;


use App\Entity\Campus;
use App\Form\model\FiltreFormModel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FiltreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('campus', EntityType::class, [
                'label'=>'Campus',
                'class' => Campus::class,
                'choice_label' => 'nom'
            ])
            ->add('nomSortie', TextType::class, ['label'=>"Nom de la sortie", 'required'=>false])
            ->add('dateDepuis', DateType::class, [
                'label'=>"Entre",
                'required'=>false,
                'widget' => 'single_text',
                'attr' => ['class' => 'js-datepicker']
                ])
            ->add('dateUntil', DateType::class, [
                'label'=>"Et",
                'required'=>false,
                'widget' => 'single_text',
                'attr' => ['class' => 'js-datepicker']
                ])
            ->add('organisateur', CheckboxType::class, ['label'=>"Sorties dont je suis organisateur/trice", 'required'=>false])
            ->add('inscrit', CheckboxType::class, ['label'=>"Sorties auxquelles je suis inscrit/e", 'required'=>false])
            ->add('pasInscrit', CheckboxType::class, ['label'=>"Sorties auxquelles je ne pas suis inscrit/e", 'required'=>false])
            ->add('passees', CheckboxType::class, ['label'=>"Sorties passées", 'required'=>false])
            ->add('submit', SubmitType::class, ['label'=>"Rechercher"])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FiltreFormModel::class
        ]);
    }
}