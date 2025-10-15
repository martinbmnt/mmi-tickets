<?php

namespace App\Form;

use App\Entity\Concert;
use App\Entity\Reservation;
use App\Enum\ReservationStatus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', EnumType::class, [
                'class' => ReservationStatus::class,
            ])
            ->add('pseudo')
            ->add('concert', EntityType::class, [
                'class' => Concert::class,
                'choice_label' => 'musicGroup',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
