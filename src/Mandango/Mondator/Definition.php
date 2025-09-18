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
 * Definitions to save with the extensions. Allows save the output.
 */
class Definition extends BaseDefinition {


	private $output;


	/**
	 * @param string $class                    The class.
	 * @param Mandango\Mondator\Output $output The output.
	 */
	public function __construct($class, Output $output) {
		parent::__construct($class);

		$this->setOutput($output);
	}


	/**
	 * Set the output.
	 *
	 * @param Mandango\Mondator\Output $output The output.
	 */
	public function setOutput(Output $output) {
		$this->output = $output;
	}


	/**
	 * Returns the output.
	 *
	 * @return Mandango\Mondator\Output The output.
	 */
	public function getOutput() {
		return $this->output;
	}


}
