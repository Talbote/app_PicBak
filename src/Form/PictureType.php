<?php

namespace App\Form;
use App\Entity\Picture;
use App\Entity\Category;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class PictureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('imageFile', VichImageType::class, [
                'label' => 'Image (JPG or PNG file)',
                'required' => false,
                'allow_delete' => true,
               /* 'delete_label' => 'Delete', */
                'download_uri' => false,
                'imagine_pattern' => 'squared_thumbail_medium'
                /* 'download_label' => 'Download'
                 'image_uri' => true,
                 'asset_helper' => true, */
            ])
            ->add('title', TextType::class)
            ->add('description', TextareaType::class)

            ->add('categories',EntityType::class,array(
                'class'=>'App:Category',
                'placeholder'=>'select a category',
                'property_path'=>'category',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        //->where('u.valide = 1')
                        ->orderBy('u.name', 'ASC');
                },

                'label_format'=>'Categories'
                )
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Picture::class,
        ]);
    }
}
