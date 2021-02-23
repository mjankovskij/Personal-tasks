<?php

namespace App\Form;

use App\Entity\Status;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class AddtaskFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('img', FileType::class, [
                'label' => 'Image',
                'required' => false,
                'data_class' => null,
                'constraints' => [
                    new File([
                        'maxSize' => '4096k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image.',
                    ])
                ],
                'attr' => [
                    'data-controller' => "task",
                    'data-action' => "change->task#image",
                ]
            ])
            ->add('task_name', null, [
                'attr' => ['class' => 'form-control col-md-12']
            ])
            ->add('status', EntityType::class, [
                'class'    => Status::class,
                'choice_label' => function (Status $status) {
                    return $status->getName();
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.id IS NOT NULL')
                        ->orderBy('c.name', 'ASC');
                },
                'attr' => ['class' => 'form-control col-md-12']
            ])
            ->add('completed_date', DateType::class, [
                'mapped' => false,
                'required' => false,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'attr' => ['class' => 'form-control col-md-12']
            ])
            // ->add('add_date', DateType::class, [
            //     'widget' => 'single_text',
            //     'format' => 'yyyy-MM-dd',
            //     'attr' => ['class' => 'form-control col-md-12']
            // ])
            ->add(
                'task_description',
                CKEditorType::class,
                [
                    'config' => [
                        'config' => 'main_config',
                    ],
                    'attr' => ['class' => 'form-control col-md-12']
                ]
            )
            ->add('id', HiddenType::class, [
                'mapped' => false
            ])
            ->add('save', ButtonType::class, [
                'attr' => [
                    'class' => 'btn btn-secondary mb-2 col-md-12',
                    'data-controller' => "task",
                    'data-action' => "click->task#save",
                ]
            ]);
    }
}
