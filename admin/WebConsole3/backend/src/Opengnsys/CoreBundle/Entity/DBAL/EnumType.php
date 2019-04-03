<?php

/*
 * This file is part of the Opengnsys Project package.
 *
 * Created by Opengnsys on 27/009/16. <info@globunet.com>
 * Copyright (c) 2015 Opengnsys Soluciones Tecnológicas, SL. All rights reserved.
 *
 */

namespace Opengnsys\CoreBundle\Entity\DBAL;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

abstract class EnumType extends Type
{
	protected $name;
	protected $values = array();

	public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
	{			
        return "varchar(40)";
	}

	public function convertToPHPValue($value, AbstractPlatform $platform)
	{
		return $value;
	}

	public function convertToDatabaseValue($value, AbstractPlatform $platform)
	{
		if ($value != null && !in_array($value, $this->values)) {
			throw new \InvalidArgumentException("Invalid '".$this->name."' value: ".$value.".");
		}
		return $value;
	}

	public function getName()
	{
		return $this->name;
	}
}
