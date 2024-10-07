<?php

namespace App\Form;

use App\Entity\Stand;
use App\Entity\TimeSlot;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('capacity')
            ->add('duration', null, [
                'widget' => 'single_text',
            ])
            ->add('timeSlot', EntityType::class, [
                'class' => TimeSlot::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Stand::class,
        ]);
    }
}
