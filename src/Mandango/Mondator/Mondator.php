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
use RuntimeException;

/**
 * Mondator.
 */
class Mondator {


	private $configClasses;

	private $extensions;


	public function __construct() {
		$this->configClasses = [];
		$this->extensions = [];
	}


	/**
	 * Set a config class.
	 *
	 * @param string $class      The class.
	 * @param array $configClass The config class.
	 */
	public function setConfigClass($class, array $configClass) {
		$this->configClasses[$class] = $configClass;
	}


	/**
	 * Set the config classes.
	 *
	 * @param array $configClasses An array of config classes (class as key and config class as value).
	 */
	public function setConfigClasses(array $configClasses) {
		$this->configClasses = [];
		foreach ($configClasses as $class => $configClass) {
			$this->setConfigClass($class, $configClass);
		}
	}


	/**
	 * Returns if a config class exists.
	 *
	 * @param string $class The class.
	 * @return bool Returns if the config class exists.
	 */
	public function hasConfigClass($class) {
		return array_key_exists($class, $this->configClasses);
	}


	/**
	 * Returns the config classes.
	 *
	 * @return array The config classes.
	 */
	public function getConfigClasses() {
		return $this->configClasses;
	}


	/**
	 * Returns a config class.
	 *
	 * @param string $class The class.
	 * @return array The config class.
	 * @throws InvalidArgumentException If the config class does not exists.
	 */
	public function getConfigClass($class) {
		if (!$this->hasConfigClass($class)) {
			throw new InvalidArgumentException(sprintf('The config class "%s" does not exists.', $class));
		}

		return $this->configClasses[$class];
	}


	/**
	 * Add a extension.
	 *
	 * @param Mandango\Mondator\Extension $extension The extension.
	 */
	public function addExtension(Extension $extension) {
		$this->extensions[] = $extension;
	}


	/**
	 * Set the extensions.
	 *
	 * @param array $extensions An array of extensions.
	 */
	public function setExtensions(array $extensions) {
		$this->extensions = [];
		foreach ($extensions as $extension) {
			$this->addExtension($extension);
		}
	}


	/**
	 * Returns the extensions.
	 *
	 * @return array The extensions.
	 */
	public function getExtensions() {
		return $this->extensions;
	}


	/**
	 * Generate the containers.
	 *
	 * @return array The containers.
	 */
	public function generateContainers() {
		$containers = [];
		$containers['_global'] = new Container();

		// global extensions
		$globalExtensions = $this->getExtensions();

		// configClasses
		$configClasses = new ArrayObject();
		foreach ($this->getConfigClasses() as $class => $configClass) {
			$configClasses[$class] = new ArrayObject($configClass);
		}

		// classes extensions
		$classesExtensions = new ArrayObject();
		$this->generateContainersClassesExtensions($globalExtensions, $classesExtensions, $configClasses);

		// config class process
		foreach ($classesExtensions as $class => $classExtensions) {
			foreach ($classExtensions as $classExtension) {
				$classExtension->configClassProcess($class, $configClasses);
			}
		}

		// pre global process
		foreach ($globalExtensions as $globalExtension) {
			$globalExtension->preGlobalProcess($configClasses, $containers['_global']);
		}

		// class process
		foreach ($classesExtensions as $class => $classExtensions) {
			$containers[$class] = $container = new Container();
			foreach ($classExtensions as $classExtension) {
				$classExtension->classProcess($class, $configClasses, $container);
			}
		}

		// post global process
		foreach ($globalExtensions as $globalExtension) {
			$globalExtension->postGlobalProcess($configClasses, $containers['_global']);
		}

		return $containers;
	}


	/**
	 * Dump containers.
	 *
	 * @param array $containers An array of containers.
	 */
	public function dumpContainers(array $containers) {

		// output
		foreach ($containers as $container) {
			foreach ($container->getDefinitions() as $name => $definition) {
				$output = $definition->getOutput($name);
				$dir = $output->getDir();


				$file = $dir . DIRECTORY_SEPARATOR . str_replace('\\', '/', $definition->getClass()) . '.php';
				$path = dirname($file);

				if (!file_exists($path) && mkdir($path, 0777, true) === false) {
					throw new RuntimeException(sprintf('Unable to create the %s directory.', $path));
				}

				if (!is_writable($path)) {
					throw new RuntimeException(sprintf('Unable to write in the %s directory.', $path));
				}

				if (!file_exists($file) || $output->getOverride()) {
					$dumper = new Dumper($definition);
					$content = $dumper->dump();

					$tmpFile = tempnam(dirname($file), basename($file));
					if (file_put_contents($tmpFile, $content) === false || !rename($tmpFile, $file)) {
						throw new RuntimeException(sprintf('Failed to write the file "%s".', $file));
					}
					chmod($file, 0666 & ~umask());
				}
			}
		}
	}


	/**
	 * Generate and dump the containers.
	 */
	public function process() {
		$this->dumpContainers($this->generateContainers());
	}


	private function generateContainersClassesExtensions(array $globalExtensions, ArrayObject $classesExtensions, ArrayObject $configClasses) {
		foreach (array_keys($configClasses->getArrayCopy()) as $class) {
			if (isset($classesExtensions[$class])) {
				continue;
			}

			$classesExtensions[$class] = $classExtensions = new ArrayObject($globalExtensions);
			$this->generateContainersNewClassExtensions($class, $classExtensions, $configClasses);

			foreach ($classExtensions as $classExtension) {
				$newConfigClasses = new ArrayObject();
				$classExtension->newConfigClassesProcess($class, $configClasses, $newConfigClasses);

				foreach ($newConfigClasses as $newClass => $newConfigClass) {
					$configClasses[$newClass] = new ArrayObject($newConfigClass);
				}

				$this->generateContainersClassesExtensions($globalExtensions, $classesExtensions, $configClasses);
			}
		}
	}


	private function generateContainersNewClassExtensions($class, $classExtensions, $configClasses, $extensions = null) {
		if ($extensions === null) {
			$extensions = $classExtensions;
		}

		foreach ($extensions as $extension) {
			$newClassExtensions = new ArrayObject();
			$extension->newClassExtensionsProcess($class, $configClasses, $newClassExtensions);

			foreach ($newClassExtensions as $newClassExtension) {
				if (!$newClassExtension instanceof ClassExtension) {
					throw new RuntimeException(sprintf(
						'Some class extension of the class "%s" in the extension "%s" is not an instance of ClassExtension.',
						$class,
						$extension::class
					));
				}
				if ($newClassExtension instanceof Extension) {
					throw new RuntimeException(sprintf(
						'Some class extension of the class "%s" in the extension "%s" is a instance of Extension, and it can be only a instance of ClassExtension.',
						$class,
						$extension::class
					));
				}

				$classExtensions[] = $newClassExtension;
				$this->generateContainersNewClassExtensions($class, $classExtensions, $configClasses, $newClassExtension);
			}
		}
	}


}
