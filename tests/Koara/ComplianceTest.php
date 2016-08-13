<?php
namespace Koara;

use Koara\Html\Html5Renderer;

class ComplianceTest extends \PHPUnit_Framework_TestCase {

	public function provider() {
		$i=0;
		$modules = scandir('testsuite/input');
		foreach ($modules as $module) {
			if($module != '.' && $module != '..' && $module != 'end2end.kd') {
				$testcases = scandir('testsuite/input/'.$module);
				foreach ($testcases as $testcase) {
					if($testcase != '.' && $testcase != '..') {
						$array[$i][0] = $module;
						$array[$i][1] = substr($testcase, 0, -3);
						$i++;
					}
				}
			}
		}
		return $array;
	}
	
// 	/**
// 	 * @dataProvider provider
// 	 */
// 	public function testKoaraToKoara($module, $testcase) {
// 		$expected = file_get_contents('testsuite/output/koara/'.$module.'/'.$testcase.'.kd');
// 		$expected = mb_convert_encoding($expected, 'UTF-8', mb_detect_encoding($expected, 'UTF-8, ISO-8859-1', true));

// 		$parser = new Parser();
// 		$document = $parser->parseFile('testsuite/input/'.$module.'/'.$testcase.'.kd');
// 		$renderer = new KoaraRenderer();
// 		$document->accept($renderer);
		
//         $this->assertEquals($expected, $renderer->getOutput());

// 	}
	
 	/**
 	 * @dataProvider provider
 	 */
 	public function testKoaraToHtml5($module, $testcase) {		
 			$html = file_get_contents('testsuite/output/html5/'.$module.'/'.$testcase.'.htm');
 	  		$html = mb_convert_encoding($html, 'UTF-8', mb_detect_encoding($html, 'UTF-8, ISO-8859-1', true));
		 
 	  		$parser = new Parser();
 	  		$document = $parser->parseFile('testsuite/input/'.$module.'/'.$testcase.'.kd');
 		
 	 		$renderer = new Html5Renderer();
 	 		$document->accept($renderer);

 	 		$this->assertEquals($html, $renderer->getOutput());
 	}
	
}