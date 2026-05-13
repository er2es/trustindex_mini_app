<?php

declare(strict_types=1);

namespace App\Form;

use App\Form\Dto\ReviewFormDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('companyName', TextType::class, [
                'label' => 'Cégnév',
                'attr' => [
                    'placeholder' => 'Kezdj el gépelni...',
                    'autocomplete' => 'off',
                    'data-autocomplete' => 'true',
                    'class' => 'form-control',
                ],
            ])
            ->add('rating', HiddenType::class, [
                'attr' => ['id' => 'rating-value'],
            ])
            ->add('reviewText', TextareaType::class, [
                'label' => 'Vélemény',
                'attr' => [
                    'placeholder' => 'Írj részletes véleményt...',
                    'rows' => 5,
                    'class' => 'form-control',
                ],
            ])
            ->add('authorEmail', EmailType::class, [
                'label' => 'E-mail cím',
                'attr' => [
                    'placeholder' => 'pelda@email.hu',
                    'class' => 'form-control',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Vélemény beküldése',
                'attr' => ['class' => 'btn btn-ti w-100 py-2 mt-2'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ReviewFormDto::class,
        ]);
    }
}
