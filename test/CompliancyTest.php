<?php
require dirname(__FILE__)."/../Parser.php";

class ComplianceTest extends PHPUnit_Framework_TestCase {

    public function provider() {
        $i=0;
        $modules = scandir(dirname(__FILE__).'/spec');
    	foreach ($modules as $module) {
            if($module != "." && $module != "..") {
              $testcases = scandir(dirname(__FILE__).'/spec/'.$module);  
              foreach ($testcases as $testcase) {
                if(substr($testcase, -3) == ".kd") {
    		     $array[$i][0] = $module;
                 $array[$i][1] = substr($testcase, 0, -3);
                }
                //$i++;
              }
            }
		}
    
        return $array;
    }

	/**
     * @dataProvider provider
     */
    public function testAdd($module, $testcase) {
        $text = file_get_contents(dirname(__FILE__)."/spec/".$module."/$testcase.kd");
    	$text = mb_convert_encoding($text, 'UTF-8', mb_detect_encoding($text, 'UTF-8, ISO-8859-1', true));
    	$html = file_get_contents(dirname(__FILE__)."/spec/".$module."/$testcase.htm");
    	$html = mb_convert_encoding($html, 'UTF-8', mb_detect_encoding($html, 'UTF-8, ISO-8859-1', true));
       
        $koara = new Koara();
        
    	$this->assertEquals($html, $text);
        print $html."\n";
    }

}
