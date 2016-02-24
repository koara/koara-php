<?php

namespace Koara\Io;

class FileReader implements Reader
{
	
	private $fileName;
	private $index;
	
	public function __construct($fileName) 
	{
		$this->fileName = $fileName;
	}
	
    public function read(&$buffer, $offset, $length) {
    	$filecontent = @file_get_contents($this->fileName, false, null, $this->index, $length * 4);
    	
    	
    	if ($filecontent !== false && mb_strlen($filecontent) > 0) {
    		$charactersRead=0;
    		for($i=0; $i < $length; $i++) {
    			$c = mb_substr($filecontent, $i, 1, 'utf-8');
    			if($c != NULL) {
	    			$buffer[$offset + $i] = $c;
	    			$this->index += strlen($c);
	    			$charactersRead++;
    			}
    		}
    		return $charactersRead;
    	}
    	return -1;
    }

}
