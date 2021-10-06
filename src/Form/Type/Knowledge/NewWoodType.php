<?php

namespace App\Form\Type\Knowledge;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\Type\Knowledge\Value\PictureValueType;
use App\Form\Type\Knowledge\Value\TextValueType;

class NewWoodType extends AbstractType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
			->add('nameValue', TextValueType::class, array(
				'choices'         => null,
				'dataConstraints' => array(
					new \App\Validator\Constraints\OneThing(array('message' => 'N\'indiquez qu\'un seul Nom français'))),
				'constraints'     => array(
					new \Symfony\Component\Validator\Constraints\Valid(),
					new \App\Validator\Constraints\UniqueWood(),
				)
			))
			->add('grainValue', PictureValueType::class, array(
				'choices'         => null,
				'dataConstraints' => null,
				'constraints'     => array(
					new \Symfony\Component\Validator\Constraints\Valid(),
				)
			))
		;
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'App\Form\Model\NewWood',
		));
	}

	public function getBlockPrefix() {
		return 'ladb_knowledge_newwood';
	}

}
