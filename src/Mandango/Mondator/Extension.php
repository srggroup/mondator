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

/**
 * Extension is the base class for extensions.
 */
abstract class Extension extends ClassExtension {


	/**
	 * Pre global process of the extension.
	 *
	 * @param ArrayObject $configClasses             The config classes.
	 * @param Mandango\Mondator\Container $container The global container.
	 */
	public function preGlobalProcess(ArrayObject $configClasses, Container $container) {
		$this->configClasses = $configClasses;
		$this->definitions = $container;

		$this->doPreGlobalProcess();

		$this->configClasses = null;
		$this->definitions = null;
	}


	/**
	 * Post global process of the extension.
	 *
	 * @param ArrayObject $configClasses             The config classes.
	 * @param Mandango\Mondator\Container $container The global container.
	 */
	public function postGlobalProcess(ArrayObject $configClasses, Container $container) {
		$this->configClasses = $configClasses;
		$this->definitions = $container;

		$this->doPostGlobalProcess();

		$this->configClasses = null;
		$this->definitions = null;
	}


	/**
	 * Do the pre global process.
	 */
	protected function doPreGlobalProcess() {
	}


	/**
	 * Do the post global process.
	 */
	protected function doPostGlobalProcess() {
	}


}
