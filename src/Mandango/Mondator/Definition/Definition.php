<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Mondator\Definition;

use InvalidArgumentException;

/**
 * Represents a definition of a class.
 */
class Definition {


	private $class;

	private $parentClass;

	private $interfaces;

	private $final;

	private $abstract;

	private $constants;

	private $properties;

	private $methods;

	private $docComment;


	/**
	 * @param string $class The class.
	 */
	public function __construct($class) {
		$this->setClass($class);
		$this->interfaces = [];
		$this->final = false;
		$this->abstract = false;
		$this->constants = [];
		$this->properties = [];
		$this->methods = [];
	}


	/**
	 * Set the class.
	 *
	 * @param string $class The class.
	 */
	public function setClass($class) {
		$this->class = $class;
	}


	/**
	 * Returns the class.
	 *
	 * @return string The class.
	 */
	public function getClass() {
		return $this->class;
	}


	/**
	 * Returns the namespace.
	 *
	 * @return string|null The namespace.
	 */
	public function getNamespace() {
		if (($pos = strrpos($this->class, '\\')) !== false) {
			return substr($this->class, 0, $pos);
		}

		return null;
	}


	/**
	 * Returns the class name.
	 *
	 * @return string|null The class name.
	 */
	public function getClassName() {
		if (($pos = strrpos($this->class, '\\')) !== false) {
			return substr($this->class, $pos + 1);
		}

		return $this->class;
	}


	/**
	 * Set the parent class.
	 *
	 * @param string $parentClass The parent class.
	 */
	public function setParentClass($parentClass) {
		$this->parentClass = $parentClass;
	}


	/**
	 * Returns the parent class.
	 *
	 * @return string The parent class.
	 */
	public function getParentClass() {
		return $this->parentClass;
	}


	/**
	 * Add an interface.
	 *
	 * @param string $interface The interface.
	 */
	public function addInterface($interface) {
		$this->interfaces[] = $interface;
	}


	/**
	 * Set the interfaces.
	 *
	 * @param array $interfaces The interfaces.
	 */
	public function setInterfaces(array $interfaces) {
		$this->interfaces = [];
		foreach ($interfaces as $interface) {
			$this->addInterface($interface);
		}
	}


	/**
	 * Returns the interfaces.
	 *
	 * @return array The interfaces.
	 */
	public function getInterfaces() {
		return $this->interfaces;
	}


	/**
	 * Set if the class is final.
	 *
	 * @param bool $final If the class is final.
	 */
	public function setFinal($final) {
		$this->final = (bool) $final;
	}


	/**
	 * Returns if the class is final.
	 *
	 * @return bool Returns if the class is final.
	 */
	public function isFinal() {
		return $this->final;
	}


	/**
	 * Set if the class is abstract.
	 *
	 * @param bool $abstract If the class is abstract.
	 */
	public function setAbstract($abstract) {
		$this->abstract = (bool) $abstract;
	}


	/**
	 * Returns if the class is abstract.
	 *
	 * @return bool If the class is abstract.
	 */
	public function isAbstract() {
		return $this->abstract;
	}


	/**
	 * Add a constant.
	 *
	 * @param Mandango\Mondator\Definition\Constant $constant The constant.
	 */
	public function addConstant(Constant $constant) {
		$this->constants[] = $constant;
	}


	/**
	 * Set the constants.
	 *
	 * @param array $constants An array of constants.
	 */
	public function setConstants(array $constants) {
		$this->constants = [];
		foreach ($constants as $constant) {
			$this->addConstant($constant);
		}
	}


	/**
	 * Returns the constants.
	 *
	 * @return array The constants.
	 */
	public function getConstants() {
		return $this->constants;
	}


	/**
	 * Returns if a constant exists by name.
	 *
	 * @param string $name The constant name.
	 * @return bool If the constant exists.
	 */
	public function hasConstantByName($name) {
		foreach ($this->constants as $constant) {
			if ($constant->getName() === $name) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Returns a constant by name.
	 *
	 * @param string $name The constant name.
	 * @return Mandango\Mondator\Definition\Constant The constant.
	 * @throws InvalidArgumentException If the constant does not exists.
	 */
	public function getConstantByName($name) {
		foreach ($this->constants as $constant) {
			if ($constant->getName() === $name) {
				return $constant;
			}
		}

		throw new InvalidArgumentException(sprintf('The constant "%s" does not exists.', $name));
	}


	/**
	 * Remove property by name.
	 *
	 * @param string $name The constant name.
	 * @throws InvalidArgumentException If the property does not exists.
	 */
	public function removeConstantByName($name) {
		foreach ($this->constants as $key => $constant) {
			if ($constant->getName() === $name) {
				unset($this->constants[$key]);

				return;
			}
		}

		throw new InvalidArgumentException(sprintf('The constant "%s" does not exists.', $name));
	}


	/**
	 * Add a property.
	 *
	 * @param Mandango\Mondator\Definition\Property $property The property.
	 */
	public function addProperty(Property $property) {
		$this->properties[] = $property;
	}


	/**
	 * Set the properties.
	 *
	 * @param array $properties An array of properties.
	 */
	public function setProperties(array $properties) {
		$this->properties = [];
		foreach ($properties as $property) {
			$this->addProperty($property);
		}
	}


	/**
	 * Returns the properties.
	 *
	 * @return array The properties.
	 */
	public function getProperties() {
		return $this->properties;
	}


	/**
	 * Returns if a property exists by name.
	 *
	 * @param string $name The property name.
	 * @return bool If the property exists.
	 */
	public function hasPropertyByName($name) {
		foreach ($this->properties as $property) {
			if ($property->getName() === $name) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Returns a property by name.
	 *
	 * @param string $name The property name.
	 * @return Mandango\Mondator\Definition\Property The property.
	 * @throws InvalidArgumentException If the property does not exists.
	 */
	public function getPropertyByName($name) {
		foreach ($this->properties as $property) {
			if ($property->getName() === $name) {
				return $property;
			}
		}

		throw new InvalidArgumentException(sprintf('The property "%s" does not exists.', $name));
	}


	/**
	 * Remove property by name.
	 *
	 * @param string $name The property name.
	 * @throws InvalidArgumentException If the property does not exists.
	 */
	public function removePropertyByName($name) {
		foreach ($this->properties as $key => $property) {
			if ($property->getName() === $name) {
				unset($this->properties[$key]);

				return;
			}
		}

		throw new InvalidArgumentException(sprintf('The property "%s" does not exists.', $name));
	}


	/**
	 * Add a method.
	 *
	 * @param Mandango\Mondator\Definition\Method $method The method.
	 */
	public function addMethod(Method $method) {
		$this->methods[] = $method;
	}


	/**
	 * Set the methods.
	 *
	 * @param array $methods An array of methods.
	 */
	public function setMethods(array $methods) {
		$this->methods = [];
		foreach ($methods as $method) {
			$this->addMethod($method);
		}
	}


	/**
	 * Returns the methods.
	 *
	 * @return array The methods.
	 */
	public function getMethods() {
		return $this->methods;
	}


	/**
	 * Returns if exists a method by name.
	 *
	 * @param string $name The method name.
	 * @return bool If the method exists.
	 */
	public function hasMethodByName($name) {
		foreach ($this->methods as $method) {
			if ($method->getName() === $name) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Return a method by name.
	 *
	 * @param string $name The method name.
	 * @return Mandango\Mondator\Definition\Method The method.
	 * @throws InvalidArgumentException If the method does not exists.
	 */
	public function getMethodByName($name) {
		foreach ($this->methods as $method) {
			if ($method->getName() === $name) {
				return $method;
			}
		}

		throw new InvalidArgumentException(sprintf('The method "%s" does not exists.', $name));
	}


	/**
	 * Remove a method by name.
	 *
	 * @param string $name The method name.
	 * @throws InvalidArgumentException If the method does not exists.
	 */
	public function removeMethodByName($name) {
		foreach ($this->methods as $key => $method) {
			if ($method->getName() === $name) {
				unset($this->methods[$key]);

				return;
			}
		}

		throw new InvalidArgumentException(sprintf('The method "%s" does not exists.', $name));
	}


	/**
	 * Set the doc comment.
	 *
	 * @param string|null $docComment The doc comment.
	 */
	public function setDocComment($docComment) {
		$this->docComment = $docComment;
	}


	/**
	 * Returns the doc comment.
	 *
	 * @return string|null The doc comment.
	 */
	public function getDocComment() {
		return $this->docComment;
	}


}
