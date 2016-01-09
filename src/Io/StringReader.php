<?php

namespace Koara\Io;

class StringReader extends Reader
{

	private $index;
	private $text;

	public function __construct($text)
	{
		$this->text = $text;
	}

	public function read(&$buffer, $offset, $length)
	{		
		if($this->index < mb_strlen($this->text)) {
			$temp = mb_substr($this->text, $this->index, $length, "utf-8");
			for($i= 0; $i < $length; $i++) {
				if($i < mb_strlen($temp)) {
					$buffer[$offset + $i] = mb_substr($temp, $i, 1);
				} 
			}
			$this->index += $length;
			return mb_strlen($temp);
		} else {
			return -1;
		}
	}
	
}
