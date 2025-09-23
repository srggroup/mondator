<?php

/*
 * This file is part of Mandango.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mandango\Mondator;

use Mandango\Mondator\Definition\Definition as BaseDefinition;

/**
 * The Mondator Dumper.
 */
class Dumper {


	private $definition;


	/**
	 * @param Mandango\Mondator\Definition\Definition $definition The definition.
	 */
	public function __construct(BaseDefinition $definition) {
		$this->setDefinition($definition);
	}


	/**
	 * Set the definition.
	 *
	 * @param Mandango\Mondator\Definition\Definition $definition The definition.
	 */
	public function setDefinition(BaseDefinition $definition) {
		$this->definition = $definition;
	}


	/**
	 * Returns the definition
	 *
	 * @return Mandango\Mondator\Definition\Definition The definition.
	 */
	public function getDefinition() {
		return $this->definition;
	}


	/**
	 * Dump the definition.
	 *
	 * @return string The PHP code of the definition.
	 */
	public function dump() {
		return $this->startFile() .
			$this->addNamespace() .
			$this->startClass() .
			$this->addConstants() .
			$this->addProperties() .
			$this->addMethods() .
			$this->endClass();
	}


	/**
	 * Export an array.
	 *
	 * Based on Symfony\Component\DependencyInjection\Dumper\PhpDumper::exportParameters
	 * http://github.com/symfony/symfony
	 *
	 * @param array $array The array.
	 * @param int $indent  The indent.
	 * @return string The array exported.
	 */
	public static function exportArray(array $array, $indent) {
		$code = [];
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$value = self::exportArray($value, $indent + 4);
			} else {
				$value = $value === null ? 'null' : var_export($value, true);
			}

			$code[] = sprintf('%s%s => %s,', str_repeat(' ', $indent), var_export($key, true), $value);
		}

		return sprintf("array(\n%s\n%s)", implode("\n", $code), str_repeat(' ', $indent - 4));
	}


	private function startFile() {
		return <<<EOF
<?php

EOF;
	}


	private function addNamespace() {
		if (!$namespace = $this->definition->getNamespace()) {
			return '';
		}

		return <<<EOF

namespace $namespace;

EOF;
	}


	private function startClass() {
		$code = "\n";

		// doc comment
		if ($docComment = $this->definition->getDocComment()) {
			$code .= $docComment . "\n";
		}

		/*
		 * declaration
		 */
		$declaration = '';

		// abstract
		if ($this->definition->isAbstract()) {
			$declaration .= 'abstract ';
		}

		// class
		$declaration .= 'class ' . $this->definition->getClassName();

		// parent class
		if ($parentClass = $this->definition->getParentClass()) {
			$declaration .= ' extends ' . '\\' . ltrim($parentClass, '\\');
		}

		// interfaces
		if ($interfaces = $this->definition->getInterfaces()) {
			$declaration .= ' implements ' . implode(', ', $interfaces);
		}

		return $code . <<<EOF
$declaration
{
EOF;
	}


	private function addConstants() {
		$code = '';

		foreach ($this->definition->getConstants() as $constant) {
			$code .= "\n    const {$constant->getName()} = {$constant->getValue()};";
		}

		return "$code\n";
	}


	private function addProperties() {
		$code = '';

		$properties = $this->definition->getProperties();
		foreach ($properties as $property) {
			$code .= "\n";

			if ($docComment = $property->getDocComment()) {
				$code .= $docComment . "\n";
			}
			$isStatic = $property->isStatic() ? 'static ' : '';

			$value = $property->getValue();
			if ($value === null) {
				$code .= <<<EOF
    $isStatic{$property->getVisibility()} \${$property->getName()};
EOF;
			} else {
				$value = is_array($property->getValue()) ? self::exportArray($property->getValue(), 8) : var_export($property->getValue(), true);

				$code .= <<<EOF
    $isStatic{$property->getVisibility()} \${$property->getName()} = $value;
EOF;
			}
		}
		if ($properties) {
			$code .= "\n";
		}

		return $code;
	}


	private function addMethods() {
		$code = '';

		foreach ($this->definition->getMethods() as $method) {
			$code .= "\n";

			// doc comment
			if ($docComment = $method->getDocComment()) {
				$code .= $docComment . "\n";
			}

			// isFinal
			$isFinal = $method->isFinal() ? 'final ' : '';

			// isStatic
			$isStatic = $method->isStatic() ? 'static ' : '';

			// abstract
			if ($method->isAbstract()) {
				$code .= <<<EOF
    abstract $isStatic{$method->getVisibility()} function {$method->getName()}({$method->getArguments()});
EOF;
			} else {
				$methodCode = trim($method->getCode());
				if ($methodCode) {
					$methodCode = '    ' . $methodCode . "\n    ";
				}
				$code .= <<<EOF
    $isFinal$isStatic{$method->getVisibility()} function {$method->getName()}({$method->getArguments()})
    {
    $methodCode}
EOF;
			}

			$code .= "\n";
		}

		return $code;
	}


	private function endClass() {
		$code = '';

		if (!$this->definition->getProperties() && !$this->definition->getMethods()) {
			$code .= "\n";
		}

		$code .= <<<EOF
}
EOF;

		return $code;
	}


}
