<?php

namespace Koara\Io;

class StringReader implements Reader
{
	
	private $text;
	private $index;
	
	public function __construct($text) 
	{
		$this->text = $text;
	}
	
    public function read(&$buffer, $offset, $length) {
    	if ($this->text !== false && mb_strlen(mb_substr($this->text, $this->index)) > 0) {
    		$charactersRead=0;
    		for($i=0; $i < $length; $i++) {
     			$c = mb_substr($this->text, $this->index + $i, 1, "utf-8");
     			if($c != NULL) {
	     			$buffer[$offset + $i] = $c;
	     			$charactersRead++;
     			}
    		}
    		$this->index += $length;
    		return $charactersRead;
    	}
    	return -1;
    }

}
