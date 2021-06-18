<?php

namespace App\Form;

use App\Entity\Flight;
use App\Entity\FlightOrder;
use App\Repository\FlightRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FlightOrderType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status')
            ->add('seatNumber', NumberType::class)
            ->add('flight', EntityType::class, ['class'=>Flight::class])
            ->add('client', ClientType::class);

        switch ($options['definition']) {
            case FlightOrder::IS_PAID:
                $builder->add('payment', PaymentType::class);
                break;
            default:
                break;
        }


        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FlightOrder::class,
            'definition'=>FlightOrder::IS_BOOKED
        ]);
    }
}
