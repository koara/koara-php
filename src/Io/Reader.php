<?php

namespace Koara\Io;

interface Reader
{
	
    public function read(&$buffer, $offset, $length);

}
