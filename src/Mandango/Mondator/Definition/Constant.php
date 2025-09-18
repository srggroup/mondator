<?php

namespace Mandango\Mondator\Definition;

class Constant {


	public function __construct(private $name, private $value) {
	}


	public function getName() {
		return $this->name;
	}


	public function getValue() {
		return $this->value;
	}


}
