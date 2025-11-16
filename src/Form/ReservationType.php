<?php

namespace App\Form;

use App\Entity\Concert;
use App\Entity\Reservation;
use App\Enum\ReservationStatus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('pseudo', TextType::class);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();

            if ($form->getConfig()->getMethod() === 'PUT') {
                // Update form
                $form->add('status', EnumType::class, [
                    'class' => ReservationStatus::class,
                ]);
            } else {
                // Create form
                $form->add('concert', EntityType::class, [
                    'class' => Concert::class,
                    'choice_label' => 'musicGroup',
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
