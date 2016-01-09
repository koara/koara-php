<?php

namespace Koara\Io;

abstract class Reader
{

	abstract public function read(&$buffer, $offset, $length);
	
}
