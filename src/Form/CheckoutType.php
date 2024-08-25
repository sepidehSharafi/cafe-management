<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CheckoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cardNumber', TextType::class, [
                'label' => 'Card Number',
                'required' => true,
            ])
            ->add('expirationDate', TextType::class, [
                'label' => 'Expiration Date (MM/YY)',
                'required' => true,
            ])
            ->add('cvv', NumberType::class, [
                'label' => 'CVV',
                'required' => true,
            ])
            ->add('address', TextType::class, [
                'label' => 'Address',
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Submit Order',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}