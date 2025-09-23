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

use ArrayObject;
use InvalidArgumentException;
use Mandango\Mondator\Definition\Method;
use Mandango\Mondator\Definition\Property;
use RuntimeException;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * ClassExtension is the base class for class extensions.
 */
abstract class ClassExtension {


	private $options;

	private $requiredOptions;

	protected $definitions;

	protected $class;

	protected ?ArrayObject $configClasses;

	protected $configClass;

	protected $newClassExtensions;

	protected $newConfigClasses;

	protected $twig;

	protected $twigTempDir;


	/**
	 * @param array $options An array of options.
	 */
	public function __construct(array $options = []) {
		$this->options = [];
		$this->requiredOptions = [];

		$this->setUp();

		foreach ($options as $name => $value) {
			$this->setOption($name, $value);
		}

		// required options
		if ($diff = array_diff($this->requiredOptions, array_keys($options))) {
			throw new RuntimeException(sprintf('%s requires the options: "%s".', static::class, implode(', ', $diff)));
		}
	}


	/**
	 * Set up the extension.
	 */
	protected function setUp() {
	}


	/**
	 * Returns if exists an option.
	 *
	 * @param string $name The name.
	 * @return bool Returns true if the option exists, false otherwise.
	 */
	public function hasOption($name) {
		return array_key_exists($name, $this->options);
	}


	/**
	 * Set an option.
	 *
	 * @param string $name  The name.
	 * @param mixed  $value The value.
	 * @throws InvalidArgumentException If the option does not exists.
	 */
	public function setOption($name, $value) {
		if (!$this->hasOption($name)) {
			throw new InvalidArgumentException(sprintf('The option "%s" does not exists.', $name));
		}

		$this->options[$name] = $value;
	}


	/**
	 * Returns the options.
	 *
	 * @return array The options.
	 */
	public function getOptions() {
		return $this->options;
	}


	/**
	 * Return an option.
	 *
	 * @param string $name The name.
	 * @return mixed The value of the option.
	 * @throws InvalidArgumentException If the options does not exists.
	 */
	public function getOption($name) {
		if (!$this->hasOption($name)) {
			throw new InvalidArgumentException(sprintf('The option "%s" does not exists.', $name));
		}

		return $this->options[$name];
	}


	/**
	 * New class extensions process.
	 *
	 * @param string       $class              The class.
	 * @param ArrayObject $configClasses      The config classes.
	 * @param ArrayObject $newClassExtensions The new class extensions.
	 */
	public function newClassExtensionsProcess($class, ArrayObject $configClasses, ArrayObject $newClassExtensions) {
		$this->class = $class;
		$this->configClasses = $configClasses;
		$this->configClass = $configClasses[$class];
		$this->newClassExtensions = $newClassExtensions;

		$this->doNewClassExtensionsProcess();

		$this->class = null;
		$this->configClasses = null;
		$this->configClass = null;
		$this->newClassExtensions = null;
	}


	/**
	 * New config classes process.
	 *
	 * @param string       $class            The class.
	 * @param ArrayObject $configClasses    The config classes.
	 * @param ArrayObject $newConfigClasses The new config classes.
	 */
	public function newConfigClassesProcess($class, ArrayObject $configClasses, ArrayObject $newConfigClasses) {
		$this->class = $class;
		$this->configClasses = $configClasses;
		$this->configClass = $configClasses[$class];
		$this->newConfigClasses = $newConfigClasses;

		$this->doNewConfigClassesProcess();

		$this->class = null;
		$this->configClasses = null;
		$this->configClass = null;
		$this->newConfigClasses = null;
	}


	/**
	 * Process the config class.
	 *
	 * @param string       $class         The class.
	 * @param ArrayObject $configClasses The config classes.
	 */
	public function configClassProcess($class, ArrayObject $configClasses) {
		$this->class = $class;
		$this->configClasses = $configClasses;
		$this->configClass = $configClasses[$class];

		$this->doConfigClassProcess();

		$this->class = null;
		$this->configClasses = null;
		$this->configClass = null;
	}


	/**
	 * Process the class.
	 *
	 * @param string                      $class         The class.
	 * @param ArrayObject                $configClasses The config classes.
	 * @param Mandango\Mondator\Container $container     The container.
	 */
	public function classProcess($class, ArrayObject $configClasses, Container $container) {
		$this->class = $class;
		$this->configClasses = $configClasses;
		$this->configClass = $configClasses[$class];
		$this->definitions = $container;

		$this->doClassProcess();

		$this->class = null;
		$this->configClasses = null;
		$this->configClass = null;
		$this->definitions = null;
	}


	public function getTwig() {
		if ($this->twig === null) {
			//@phpcs:ignore WebimpressCodingStandard.Formatting.StringClassReference.Found
			if (!class_exists('\Twig\Environment')) {
				throw new RuntimeException('Twig is required to use templates.');
			}

			$loader = new ArrayLoader();
			$twig = new Environment($loader, [
				'autoescape'       => false,
				'strict_variables' => true,
				'debug'            => true,
				'cache'            => $this->twigTempDir = sys_get_temp_dir() . '/' . uniqid('mondator_'),
			]);

			$this->configureTwig($twig);

			$this->twig = $twig;
		}

		return $this->twig;
	}


	/**
	 * Add an option.
	 *
	 * @param string $name         The option name.
	 * @param mixed  $defaultValue The default value (optional, null by default).
	 */
	protected function addOption($name, $defaultValue = null) {
		$this->options[$name] = $defaultValue;
	}


	/**
	 * Add options.
	 *
	 * @param array $options An array with options (name as key and default value as value).
	 */
	protected function addOptions(array $options) {
		foreach ($options as $name => $defaultValue) {
			$this->addOption($name, $defaultValue);
		}
	}


	/**
	 * Add a required option.
	 *
	 * @param string $name The option name.
	 */
	protected function addRequiredOption($name) {
		$this->addOption($name);

		$this->requiredOptions[] = $name;
	}


	/**
	 * Add required options.
	 *
	 * @param array $options An array with the name of the required option as value.
	 */
	protected function addRequiredOptions(array $options) {
		foreach ($options as $name) {
			$this->addRequiredOption($name);
		}
	}


	/**
	 * Do the new class extensions process.
	 *
	 * Here you can add new class extensions.
	 */
	protected function doNewClassExtensionsProcess() {
	}


	/**
	 * Do the new config classes process.
	 *
	 * Here you can add new config classes, and change the config classes
	 * if it is necessary to build the new config classes.
	 */
	protected function doNewConfigClassesProcess() {
	}


	/**
	 * Do the config class process.
	 *
	 * Here you can modify the config class.
	 */
	protected function doConfigClassProcess() {
	}


	/**
	 * Do the class process.
	 */
	protected function doClassProcess() {
	}


	/**
	 * Twig.
	 */
	protected function processTemplate(Definition $definition, $name, array $variables = []) {
		$twig = $this->getTwig();

		$variables['extension'] = $this;
		$variables['options'] = $this->options;
		$variables['class'] = $this->class;
		$variables['config_class'] = $this->configClass;
		$variables['config_classes'] = $this->configClasses;

		$template = $twig->createTemplate($name);
		$result = $template->render($variables);

		// properties
		$expression = '/
            (?P<docComment>\ \ \ \ \/\*\*\n[\s\S]*\ \ \ \ \ \*\/)?\n?
             \ \ \ \ (?P<static>static\ )?
            (?P<visibility>public|protected|private)
            \s
            \$
            (?P<name>[a-zA-Z0-9_]+)
            (?P<value>\={1}[^;]*)
            ;
        /xU';
		preg_match_all($expression, $result, $matches);

		$matchesCount = count($matches[0]);
		for ($i = 0; $i <= $matchesCount - 1; $i++) {
			$value = null;
			$result = str_replace($matches[0][$i], '', $result);
			if (!empty($matches['value'][$i])) {
				//@phpcs:ignore Squiz.PHP.Eval.Discouraged
				eval('$value' . $matches['value'][$i] . ';');
			}
			$property = new Property($matches['visibility'][$i], $matches['name'][$i], $value);
			if ($matches['static'][$i]) {
				$property->setStatic(true);
			}
			if ($matches['docComment'][$i]) {
				$property->setDocComment($matches['docComment'][$i]);
			}
			$definition->addProperty($property);
		}

		// methods
		$expression = '/
            (?P<docComment>\ \ \ \ \/\*\*\n[\s\S]*\ \ \ \ \ \*\/)?\n
            \ \ \ \ (?P<static>static\ )?
            (?P<visibility>public|protected|private)
            \s
            function
            \s
            (?P<name>[a-zA-Z0-9_]+)
            \((?P<arguments>[$a-zA-Z0-9_\\\=\(\), ]*)\)
            \n
            \ \ \ \ \{
                (?P<code>[\s\S]*)
            \n\ \ \ \ \}
        /xU';
		preg_match_all($expression, $result, $matches);

		$matchesCount = count($matches[0]);
		for ($i = 0; $i <= $matchesCount - 1; $i++) {
			$code = trim($matches['code'][$i], "\n");
			$method = new Method($matches['visibility'][$i], $matches['name'][$i], $matches['arguments'][$i], $code);
			if ($matches['static'][$i]) {
				$method->setStatic(true);
			}
			if ($matches['docComment'][$i]) {
				$method->setDocComment($matches['docComment'][$i]);
			}
			$definition->addMethod($method);
		}
	}


	protected function configureTwig(Environment $twig) {
	}


	protected function createClassExtensionFromArray(array $data) {
		if (!isset($data['class'])) {
			throw new InvalidArgumentException(sprintf('The extension does not have class.'));
		}

		return new $data['class']($data['options'] ?? []);
	}


	private function removeDir($target) {
		$fp = opendir($target);
		while (($file = readdir($fp)) !== false) {
			if (in_array($file, ['.', '..'])) {
				continue;
			}

			if (is_dir($target . '/' . $file)) {
				self::removeDir($target . '/' . $file);
			} else {
				unlink($target . '/' . $file);
			}
		}
		closedir($fp);
		rmdir($target);
	}


	public function __destruct() {
		if ($this->twigTempDir && is_dir($this->twigTempDir)) {
			$this->removeDir($this->twigTempDir);
		}
	}


}
