<?php
namespace Koara;

use Koara\Renderer\Html5Renderer;

class ComplianceTest extends \PHPUnit_Framework_TestCase {

	public function provider() {
		$i=0;
		$modules = scandir(dirname(__FILE__).'/testsuite');
		foreach ($modules as $module) {
			if($module != '.' && $module != '..' && substr($module, 0, 1) != '_') {
				$testcases = scandir(dirname(__FILE__).'/testsuite/'.$module.'/koara');
				foreach ($testcases as $testcase) {
					if(substr($testcase, -3) == '.kd') {
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
			$html = file_get_contents(dirname(__FILE__).'/testsuite/'.$module.'/html5/'.$testcase.'.htm');
	  		$html = mb_convert_encoding($html, 'UTF-8', mb_detect_encoding($html, 'UTF-8, ISO-8859-1', true));
		 
	  		$parser = new Parser();
	  		$document = $parser->parseFile(dirname(__FILE__).'/testsuite/'.$module.'/koara/'.$testcase.'.kd');
 		
	 		$renderer = new Html5Renderer();
	 		$document->accept($renderer);
	 		
	 		$this->assertEquals($html, $renderer->getOutput());
	}

}