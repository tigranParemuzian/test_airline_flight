<?php

namespace App\DoctrineFilter;

use Doctrine\ORM\Query\Filter\SQLFilter;
use Doctrine\ORM\Mapping\ClassMetaData;
use phpDocumentor\Reflection\Types\Void_;

/**
 * Class OperatorFilter
 * @package AppBundle\DoctrineFilter
 */
class EnableAvailableFilter extends SQLFilter
{
	/**
	 * This Doctrine filter return only published Items
	 *
	 * @param ClassMetaData $targetEntity
	 * @param $targetTableAlias
	 * @return string
	 */
	public function addFilterConstraint(ClassMetaData $targetEntity, $targetTableAlias)
	{
		if ($targetEntity->reflClass->implementsInterface('App\Interfaces\EnableInterface')) {

			return  $targetTableAlias . '.enabled = 1';
		}

        return "";
	}
}