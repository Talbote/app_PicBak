<?php

namespace App\Form;

use App\Data\SearchData;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        if ($options['premium_categories_required']) {
            $builder
                ->add('categories', EntityType::class, [
                    'label' => false,
                    'required' => false,
                    'class' => Category::class,
                    'expanded' => true,
                    'multiple' => true,
                ]);
        }

        $builder
            ->add('q', TextType::class, [
                'label' => false,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
            'data_class' => SearchData::class,
            'method' => 'GET',
            'csrf_protection' => false,
            'premium_categories_required' => false
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
