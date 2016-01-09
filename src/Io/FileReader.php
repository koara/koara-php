<?php
 
namespace Koara\Io;

class FileReader extends Reader
{

	private $fileName;
	private $index = 0;
	
	public function __construct($fileName)
	{
		$this->fileName = $fileName;
	}
	
	public function read(&$buffer, $offset, $length)
	{
		$filecontent = @file_get_contents($this->fileName, false, null, $this->index, $length + 4);
		
		
		
		if ($filecontent !== false && mb_strlen($filecontent) > 0) {	
			
			
// 			echo "\n- ".$filecontent;
// 			$temp = mb_strcut($filecontent, min(max(0, $this->index), 4), $length, "utf-8");
// 			echo "\n# ".$temp;

			//temp in loop?
			
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
