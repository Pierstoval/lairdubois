<?php

namespace App\Form\Type\Knowledge\Value;

use App\Form\DataTransformer\PictureToIdTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PictureValueType extends AbstractValueType {

	private $om;

	public function __construct(EntityManagerInterface $om) {
		$this->om = $om;
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		parent::buildForm($builder, $options);
		$builder
			->add($builder
					->create('data', TextType::class, array( 'attr' => array( 'class' => 'ladb-pseudo-hidden' ) ))
					->addModelTransformer(new PictureToIdTransformer($this->om))
			)
		;
	}

	public function configureOptions(OptionsResolver $resolver) {
		parent::configureOptions($resolver);
		$resolver->setDefaults(array(
			'data_class' => 'App\Entity\Knowledge\Value\Picture',
		));
	}

	public function getBlockPrefix() {
		return 'ladb_knowledge_value_picture';
	}

}
