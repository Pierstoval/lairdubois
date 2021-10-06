<?php

namespace App\Validator\Constraints;

use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Knowledge\Value\Text;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use App\Entity\Knowledge\School;

class UniqueSchoolValidator extends ConstraintValidator {

	protected $om;

	public function __construct(ManagerRegistry $om) {
		$this->om = $om;
	}

	/**
	 * Checks if the passed value is valid.
	 *
	 * @param mixed $value      The value that should be validated
	 * @param Constraint $constraint The constraint for the validation
	 *
	 * @api
	 */
	public function validate($value, Constraint $constraint) {
		if (!is_null($value)) {
			if ($value instanceof Text) {
				$data = $value->getData();
				$schoolRepository = $this->om->getRepository(School::CLASS_NAME);
				if (!is_null($data) && $schoolRepository->existsByName($data, $constraint->excludedId)) {
					$this->context->buildViolation($constraint->message)
						->atPath('name')
						->addViolation();
				}
			}
		}
	}

}