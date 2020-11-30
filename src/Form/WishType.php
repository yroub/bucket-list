<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Wish;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WishType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, ['label' => 'Your idea'])
            ->add('description', null, ['label' => 'Please describe it!', 'required' => false])
            ->add('author', null, ['label' => 'Your username'])
            ->add('category', EntityType::class, [
                'label' => 'Category',
                //quelle est la classe à afficher ici ?
                'class' => Category::class,
                //quelle propriété utiliser pour les <option> dans la liste déroulante ?
                'choice_label' => 'name'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Wish::class,
        ]);
    }
}
