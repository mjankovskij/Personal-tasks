<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AddstatusFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => ['class' => 'form-control col-md-12']
            ])
            ->add('id', HiddenType::class, [
            'mapped' => false])
            ->add('save', ButtonType::class, [
                'attr' => [
                    'class' => 'btn btn-secondary mb-2 col-md-12',
                    'data-controller' => "status",
                    'data-action' => "click->status#save",
                ]
            ]);
    }
}
