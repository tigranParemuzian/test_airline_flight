<?php

namespace App\Form;

use App\Entity\Flight;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FlightType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startedAt', DateTimeType::class, ['widget'=>'single_text'])
            ->add('finishedAt', DateTimeType::class, ['widget'=>'single_text'])
            ->add('seatCount', NumberType::class)
            ->add('name', TextType::class)
            ->add('fromLocation', AddressType::class)
            ->add('toLocation', AddressType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Flight::class,
            'validation_groups'=>['add-flight', 'default'],
//            'csr'
        ]);
    }
}
