<?php

namespace App\Form;

use App\Form\model\ImportCsvFormModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ImportCsvType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('csv', FileType::class,
                ['label'=>'Mon CSV',
                    'required'=>false,
                    'mapped'=> false,
                    'constraints'=>[
                        new File()]
                ])
            ->add('submit', SubmitType::class, ['label'=>"Ajouter",
                'attr' => ['class' => 'btn btn-outline-secondary btn-submit btn-form']
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ImportCsvFormModel::class
        ]);
    }
}