<?php
namespace Koara;

use Koara\Renderer\Html5Renderer;
use Koara\Io\FileReader;

class ComplianceTest extends \PHPUnit_Framework_TestCase {

	public function provider() {
		$i=0;
		$modules = scandir(dirname(__FILE__).'/resources/spec');
		foreach ($modules as $module) {
			if($module != "." && $module != "..") {
				$testcases = scandir(dirname(__FILE__).'/resources/spec/'.$module);
				foreach ($testcases as $testcase) {
					if(substr($testcase, -3) == ".kd") {
						$array[$i][0] = $module;
						$array[$i][1] = substr($testcase, 0, -3);
					}
					$i++;
				}
			}
		}
		return $array;
	}
	
	/**
	 * @dataProvider provider
	 */
	public function testAdd($module, $testcase) {
  			$html = file_get_contents(dirname(__FILE__)."/resources/spec/".$module."/$testcase.htm");
	  		$html = mb_convert_encoding($html, 'UTF-8', mb_detect_encoding($html, 'UTF-8, ISO-8859-1', true));
		 
	  		$parser = new Parser();
	  		$document = $parser->parseFile(dirname(__FILE__)."/resources/spec/".$module."/$testcase.kd");
 		
	 		$renderer = new Html5Renderer();
	 		$document->accept($renderer);
	 		
	 		//var_dump($document);
	 		
	 		//echo "\n".$renderer->getOutput();
	 		
	 		$this->assertEquals($html, $renderer->getOutput());
	 		
	}

}