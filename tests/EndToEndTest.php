<?php

namespace Koara;

use Koara\Renderer\Html5Renderer;

class EndToEndTest extends \PHPUnit_Framework_TestCase {
	
	
	public function testScenario000001() {
		$this->assertOutput("scenario000001", Module::PARAGRAPHS );
	}
	
	public function testScenario000002() {
		$this->assertOutput("scenario000002", Module::HEADINGS );
	}
	
	public function testScenario000003() {
		$this->assertOutput("scenario000003", Module::PARAGRAPHS, Module::HEADINGS );
	}
	
	public function testScenario000004() {
		$this->assertOutput("scenario000004", Module::LISTS );
	}
	
	public function testScenario000005() {
		$this->assertOutput("scenario000005", Module::PARAGRAPHS, Module::LISTS );
	}
	
	public function testScenario000006() {
		$this->assertOutput("scenario000006", Module::HEADINGS, Module::LISTS );
	}
	
	public function testScenario000007() {
		$this->assertOutput("scenario000007", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS );
	}
	
	public function testScenario000008() {
		$this->assertOutput("scenario000008", Module::LINKS );
	}
	
	public function testScenario000009() {
		$this->assertOutput("scenario000009", Module::PARAGRAPHS, Module::LINKS );
	}
	
	public function testScenario000010() {
		$this->assertOutput("scenario000010", Module::HEADINGS, Module::LINKS );
	}
	
	public function testScenario000011() {
		$this->assertOutput("scenario000011", Module::PARAGRAPHS, Module::HEADINGS, Module::LINKS );
	}
	
	public function testScenario000012() {
		$this->assertOutput("scenario000012", Module::LISTS, Module::LINKS );
	}
	
	public function testScenario000013() {
		$this->assertOutput("scenario000013", Module::PARAGRAPHS, Module::LISTS, Module::LINKS );
	}
	
	public function testScenario000014() {
		$this->assertOutput("scenario000014", Module::HEADINGS, Module::LISTS, Module::LINKS );
	}
	
	public function testScenario000015() {
		$this->assertOutput("scenario000015", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::LINKS );
	}
	
	public function testScenario000016() {
		$this->assertOutput("scenario000016", Module::IMAGES );
	}
	
	public function testScenario000017() {
		$this->assertOutput("scenario000017", Module::PARAGRAPHS, Module::IMAGES );
	}
	
	public function testScenario000018() {
		$this->assertOutput("scenario000018", Module::HEADINGS, Module::IMAGES );
	}
	
	public function testScenario000019() {
		$this->assertOutput("scenario000019", Module::PARAGRAPHS, Module::HEADINGS, Module::IMAGES );
	}
	
	public function testScenario000020() {
		$this->assertOutput("scenario000020", Module::LISTS, Module::IMAGES );
	}
	
	public function testScenario000021() {
		$this->assertOutput("scenario000021", Module::PARAGRAPHS, Module::LISTS, Module::IMAGES );
	}
	
	public function testScenario000022() {
		$this->assertOutput("scenario000022", Module::HEADINGS, Module::LISTS, Module::IMAGES );
	}
	
	public function testScenario000023() {
		$this->assertOutput("scenario000023", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::IMAGES );
	}
	
	public function testScenario000024() {
		$this->assertOutput("scenario000024", Module::LINKS, Module::IMAGES );
	}
	
	public function testScenario000025() {
		$this->assertOutput("scenario000025", Module::PARAGRAPHS, Module::LINKS, Module::IMAGES );
	}
	
	public function testScenario000026() {
		$this->assertOutput("scenario000026", Module::HEADINGS, Module::LINKS, Module::IMAGES );
	}
	
	public function testScenario000027() {
		$this->assertOutput("scenario000027", Module::PARAGRAPHS, Module::HEADINGS, Module::LINKS, Module::IMAGES );
	}
	
	public function testScenario000028() {
		$this->assertOutput("scenario000028", Module::LISTS, Module::LINKS, Module::IMAGES );
	}
	
	public function testScenario000029() {
		$this->assertOutput("scenario000029", Module::PARAGRAPHS, Module::LISTS, Module::LINKS, Module::IMAGES );
	}
	
	public function testScenario000030() {
		$this->assertOutput("scenario000030", Module::HEADINGS, Module::LISTS, Module::LINKS, Module::IMAGES );
	}
	
	public function testScenario000031() {
		$this->assertOutput("scenario000031", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::LINKS, Module::IMAGES );
	}
	
	public function testScenario000032() {
		$this->assertOutput("scenario000032", Module::FORMATTING );
	}
	
	public function testScenario000033() {
		$this->assertOutput("scenario000033", Module::PARAGRAPHS, Module::FORMATTING );
	}
	
	public function testScenario000034() {
		$this->assertOutput("scenario000034", Module::HEADINGS, Module::FORMATTING );
	}
	
	public function testScenario000035() {
		$this->assertOutput("scenario000035", Module::PARAGRAPHS, Module::HEADINGS, Module::FORMATTING );
	}
	
	public function testScenario000036() {
		$this->assertOutput("scenario000036", Module::LISTS, Module::FORMATTING );
	}
	
	public function testScenario000037() {
		$this->assertOutput("scenario000037", Module::PARAGRAPHS, Module::LISTS, Module::FORMATTING );
	}
	
	public function testScenario000038() {
		$this->assertOutput("scenario000038", Module::HEADINGS, Module::LISTS, Module::FORMATTING );
	}
	
	public function testScenario000039() {
		$this->assertOutput("scenario000039", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::FORMATTING );
	}
	
	public function testScenario000040() {
		$this->assertOutput("scenario000040", Module::LINKS, Module::FORMATTING );
	}
	
	public function testScenario000041() {
		$this->assertOutput("scenario000041", Module::PARAGRAPHS, Module::LINKS, Module::FORMATTING );
	}
	
	public function testScenario000042() {
		$this->assertOutput("scenario000042", Module::HEADINGS, Module::LINKS, Module::FORMATTING );
	}
	
	public function testScenario000043() {
		$this->assertOutput("scenario000043", Module::PARAGRAPHS, Module::HEADINGS, Module::LINKS, Module::FORMATTING );
	}
	
	public function testScenario000044() {
		$this->assertOutput("scenario000044", Module::LISTS, Module::LINKS, Module::FORMATTING );
	}
	
	public function testScenario000045() {
		$this->assertOutput("scenario000045", Module::PARAGRAPHS, Module::LISTS, Module::LINKS, Module::FORMATTING );
	}
	
	public function testScenario000046() {
		$this->assertOutput("scenario000046", Module::HEADINGS, Module::LISTS, Module::LINKS, Module::FORMATTING );
	}
	
	public function testScenario000047() {
		$this->assertOutput("scenario000047", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::LINKS, Module::FORMATTING );
	}
	
	public function testScenario000048() {
		$this->assertOutput("scenario000048", Module::IMAGES, Module::FORMATTING );
	}
	
	public function testScenario000049() {
		$this->assertOutput("scenario000049", Module::PARAGRAPHS, Module::IMAGES, Module::FORMATTING );
	}
	
	public function testScenario000050() {
		$this->assertOutput("scenario000050", Module::HEADINGS, Module::IMAGES, Module::FORMATTING );
	}
	
	public function testScenario000051() {
		$this->assertOutput("scenario000051", Module::PARAGRAPHS, Module::HEADINGS, Module::IMAGES, Module::FORMATTING );
	}
	
	public function testScenario000052() {
		$this->assertOutput("scenario000052", Module::LISTS, Module::IMAGES, Module::FORMATTING );
	}
	
	public function testScenario000053() {
		$this->assertOutput("scenario000053", Module::PARAGRAPHS, Module::LISTS, Module::IMAGES, Module::FORMATTING );
	}
	
	public function testScenario000054() {
		$this->assertOutput("scenario000054", Module::HEADINGS, Module::LISTS, Module::IMAGES, Module::FORMATTING );
	}
	
	public function testScenario000055() {
		$this->assertOutput("scenario000055", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::IMAGES, Module::FORMATTING );
	}
	
	public function testScenario000056() {
		$this->assertOutput("scenario000056", Module::LINKS, Module::IMAGES, Module::FORMATTING );
	}
	
	public function testScenario000057() {
		$this->assertOutput("scenario000057", Module::PARAGRAPHS, Module::LINKS, Module::IMAGES, Module::FORMATTING );
	}
	
	public function testScenario000058() {
		$this->assertOutput("scenario000058", Module::HEADINGS, Module::LINKS, Module::IMAGES, Module::FORMATTING );
	}
	
	public function testScenario000059() {
		$this->assertOutput("scenario000059", Module::PARAGRAPHS, Module::HEADINGS, Module::LINKS, Module::IMAGES, Module::FORMATTING );
	}
	
	public function testScenario000060() {
		$this->assertOutput("scenario000060", Module::LISTS, Module::LINKS, Module::IMAGES, Module::FORMATTING );
	}
	
	public function testScenario000061() {
		$this->assertOutput("scenario000061", Module::PARAGRAPHS, Module::LISTS, Module::LINKS, Module::IMAGES, Module::FORMATTING );
	}
	
	public function testScenario000062() {
		$this->assertOutput("scenario000062", Module::HEADINGS, Module::LISTS, Module::LINKS, Module::IMAGES, Module::FORMATTING );
	}
	
	public function testScenario000063() {
		$this->assertOutput("scenario000063", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::LINKS, Module::IMAGES, Module::FORMATTING );
	}
	
	public function testScenario000064() {
		$this->assertOutput("scenario000064", Module::BLOCKQUOTES );
	}
	
	public function testScenario000065() {
		$this->assertOutput("scenario000065", Module::PARAGRAPHS, Module::BLOCKQUOTES );
	}
	
	public function testScenario000066() {
		$this->assertOutput("scenario000066", Module::HEADINGS, Module::BLOCKQUOTES );
	}
	
	public function testScenario000067() {
		$this->assertOutput("scenario000067", Module::PARAGRAPHS, Module::HEADINGS, Module::BLOCKQUOTES );
	}
	
	public function testScenario000068() {
		$this->assertOutput("scenario000068", Module::LISTS, Module::BLOCKQUOTES );
	}
	
	public function testScenario000069() {
		$this->assertOutput("scenario000069", Module::PARAGRAPHS, Module::LISTS, Module::BLOCKQUOTES );
	}
	
	public function testScenario000070() {
		$this->assertOutput("scenario000070", Module::HEADINGS, Module::LISTS, Module::BLOCKQUOTES );
	}
	
	public function testScenario000071() {
		$this->assertOutput("scenario000071", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::BLOCKQUOTES );
	}
	
	public function testScenario000072() {
		$this->assertOutput("scenario000072", Module::LINKS, Module::BLOCKQUOTES );
	}
	
	public function testScenario000073() {
		$this->assertOutput("scenario000073", Module::PARAGRAPHS, Module::LINKS, Module::BLOCKQUOTES );
	}
	
	public function testScenario000074() {
		$this->assertOutput("scenario000074", Module::HEADINGS, Module::LINKS, Module::BLOCKQUOTES );
	}
	
	public function testScenario000075() {
		$this->assertOutput("scenario000075", Module::PARAGRAPHS, Module::HEADINGS, Module::LINKS, Module::BLOCKQUOTES );
	}
	
	public function testScenario000076() {
		$this->assertOutput("scenario000076", Module::LISTS, Module::LINKS, Module::BLOCKQUOTES );
	}
	
	public function testScenario000077() {
		$this->assertOutput("scenario000077", Module::PARAGRAPHS, Module::LISTS, Module::LINKS, Module::BLOCKQUOTES );
	}
	
	public function testScenario000078() {
		$this->assertOutput("scenario000078", Module::HEADINGS, Module::LISTS, Module::LINKS, Module::BLOCKQUOTES );
	}
	
	public function testScenario000079() {
		$this->assertOutput("scenario000079", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::LINKS, Module::BLOCKQUOTES );
	}
	
	public function testScenario000080() {
		$this->assertOutput("scenario000080", Module::IMAGES, Module::BLOCKQUOTES );
	}
	
	public function testScenario000081() {
		$this->assertOutput("scenario000081", Module::PARAGRAPHS, Module::IMAGES, Module::BLOCKQUOTES );
	}
	
	public function testScenario000082() {
		$this->assertOutput("scenario000082", Module::HEADINGS, Module::IMAGES, Module::BLOCKQUOTES );
	}
	
	public function testScenario000083() {
		$this->assertOutput("scenario000083", Module::PARAGRAPHS, Module::HEADINGS, Module::IMAGES, Module::BLOCKQUOTES );
	}
	
	public function testScenario000084() {
		$this->assertOutput("scenario000084", Module::LISTS, Module::IMAGES, Module::BLOCKQUOTES );
	}
	
	public function testScenario000085() {
		$this->assertOutput("scenario000085", Module::PARAGRAPHS, Module::LISTS, Module::IMAGES, Module::BLOCKQUOTES );
	}
	
	public function testScenario000086() {
		$this->assertOutput("scenario000086", Module::HEADINGS, Module::LISTS, Module::IMAGES, Module::BLOCKQUOTES );
	}
	
	public function testScenario000087() {
		$this->assertOutput("scenario000087", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::IMAGES, Module::BLOCKQUOTES );
	}
	
	public function testScenario000088() {
		$this->assertOutput("scenario000088", Module::LINKS, Module::IMAGES, Module::BLOCKQUOTES );
	}
	
	public function testScenario000089() {
		$this->assertOutput("scenario000089", Module::PARAGRAPHS, Module::LINKS, Module::IMAGES, Module::BLOCKQUOTES );
	}
	
	public function testScenario000090() {
		$this->assertOutput("scenario000090", Module::HEADINGS, Module::LINKS, Module::IMAGES, Module::BLOCKQUOTES );
	}
	
	public function testScenario000091() {
		$this->assertOutput("scenario000091", Module::PARAGRAPHS, Module::HEADINGS, Module::LINKS, Module::IMAGES, Module::BLOCKQUOTES );
	}
	
	public function testScenario000092() {
		$this->assertOutput("scenario000092", Module::LISTS, Module::LINKS, Module::IMAGES, Module::BLOCKQUOTES );
	}
	
	public function testScenario000093() {
		$this->assertOutput("scenario000093", Module::PARAGRAPHS, Module::LISTS, Module::LINKS, Module::IMAGES, Module::BLOCKQUOTES );
	}
	
	public function testScenario000094() {
		$this->assertOutput("scenario000094", Module::HEADINGS, Module::LISTS, Module::LINKS, Module::IMAGES, Module::BLOCKQUOTES );
	}
	
	public function testScenario000095() {
		$this->assertOutput("scenario000095", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::LINKS, Module::IMAGES, Module::BLOCKQUOTES );
	}
	
	public function testScenario000096() {
		$this->assertOutput("scenario000096", Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000097() {
		$this->assertOutput("scenario000097", Module::PARAGRAPHS, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000098() {
		$this->assertOutput("scenario000098", Module::HEADINGS, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000099() {
		$this->assertOutput("scenario000099", Module::PARAGRAPHS, Module::HEADINGS, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000100() {
		$this->assertOutput("scenario000100", Module::LISTS, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000101() {
		$this->assertOutput("scenario000101", Module::PARAGRAPHS, Module::LISTS, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000102() {
		$this->assertOutput("scenario000102", Module::HEADINGS, Module::LISTS, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000103() {
		$this->assertOutput("scenario000103", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000104() {
		$this->assertOutput("scenario000104", Module::LINKS, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000105() {
		$this->assertOutput("scenario000105", Module::PARAGRAPHS, Module::LINKS, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000106() {
		$this->assertOutput("scenario000106", Module::HEADINGS, Module::LINKS, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000107() {
		$this->assertOutput("scenario000107", Module::PARAGRAPHS, Module::HEADINGS, Module::LINKS, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000108() {
		$this->assertOutput("scenario000108", Module::LISTS, Module::LINKS, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000109() {
		$this->assertOutput("scenario000109", Module::PARAGRAPHS, Module::LISTS, Module::LINKS, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000110() {
		$this->assertOutput("scenario000110", Module::HEADINGS, Module::LISTS, Module::LINKS, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000111() {
		$this->assertOutput("scenario000111", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::LINKS, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000112() {
		$this->assertOutput("scenario000112", Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000113() {
		$this->assertOutput("scenario000113", Module::PARAGRAPHS, Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000114() {
		$this->assertOutput("scenario000114", Module::HEADINGS, Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000115() {
		$this->assertOutput("scenario000115", Module::PARAGRAPHS, Module::HEADINGS, Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000116() {
		$this->assertOutput("scenario000116", Module::LISTS, Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000117() {
		$this->assertOutput("scenario000117", Module::PARAGRAPHS, Module::LISTS, Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000118() {
		$this->assertOutput("scenario000118", Module::HEADINGS, Module::LISTS, Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000119() {
		$this->assertOutput("scenario000119", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000120() {
		$this->assertOutput("scenario000120", Module::LINKS, Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000121() {
		$this->assertOutput("scenario000121", Module::PARAGRAPHS, Module::LINKS, Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000122() {
		$this->assertOutput("scenario000122", Module::HEADINGS, Module::LINKS, Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000123() {
		$this->assertOutput("scenario000123", Module::PARAGRAPHS, Module::HEADINGS, Module::LINKS, Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000124() {
		$this->assertOutput("scenario000124", Module::LISTS, Module::LINKS, Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000125() {
		$this->assertOutput("scenario000125", Module::PARAGRAPHS, Module::LISTS, Module::LINKS, Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000126() {
		$this->assertOutput("scenario000126", Module::HEADINGS, Module::LISTS, Module::LINKS, Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000127() {
		$this->assertOutput("scenario000127", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::LINKS, Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES );
	}
	
	public function testScenario000128() {
		$this->assertOutput("scenario000128", Module::CODE );
	}
	
	public function testScenario000129() {
		$this->assertOutput("scenario000129", Module::PARAGRAPHS, Module::CODE );
	}
	
	public function testScenario000130() {
		$this->assertOutput("scenario000130", Module::HEADINGS, Module::CODE );
	}
	
	public function testScenario000131() {
		$this->assertOutput("scenario000131", Module::PARAGRAPHS, Module::HEADINGS, Module::CODE );
	}
	
	public function testScenario000132() {
		$this->assertOutput("scenario000132", Module::LISTS, Module::CODE );
	}
	
	public function testScenario000133() {
		$this->assertOutput("scenario000133", Module::PARAGRAPHS, Module::LISTS, Module::CODE );
	}
	
	public function testScenario000134() {
		$this->assertOutput("scenario000134", Module::HEADINGS, Module::LISTS, Module::CODE );
	}
	
	public function testScenario000135() {
		$this->assertOutput("scenario000135", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::CODE );
	}
	
	public function testScenario000136() {
		$this->assertOutput("scenario000136", Module::LINKS, Module::CODE );
	}
	
	public function testScenario000137() {
		$this->assertOutput("scenario000137", Module::PARAGRAPHS, Module::LINKS, Module::CODE );
	}
	
	public function testScenario000138() {
		$this->assertOutput("scenario000138", Module::HEADINGS, Module::LINKS, Module::CODE );
	}
	
	public function testScenario000139() {
		$this->assertOutput("scenario000139", Module::PARAGRAPHS, Module::HEADINGS, Module::LINKS, Module::CODE );
	}
	
	public function testScenario000140() {
		$this->assertOutput("scenario000140", Module::LISTS, Module::LINKS, Module::CODE );
	}
	
	public function testScenario000141() {
		$this->assertOutput("scenario000141", Module::PARAGRAPHS, Module::LISTS, Module::LINKS, Module::CODE );
	}
	
	public function testScenario000142() {
		$this->assertOutput("scenario000142", Module::HEADINGS, Module::LISTS, Module::LINKS, Module::CODE );
	}
	
	public function testScenario000143() {
		$this->assertOutput("scenario000143", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::LINKS, Module::CODE );
	}
	
	public function testScenario000144() {
		$this->assertOutput("scenario000144", Module::IMAGES, Module::CODE );
	}
	
	public function testScenario000145() {
		$this->assertOutput("scenario000145", Module::PARAGRAPHS, Module::IMAGES, Module::CODE );
	}
	
	public function testScenario000146() {
		$this->assertOutput("scenario000146", Module::HEADINGS, Module::IMAGES, Module::CODE );
	}
	
	public function testScenario000147() {
		$this->assertOutput("scenario000147", Module::PARAGRAPHS, Module::HEADINGS, Module::IMAGES, Module::CODE );
	}
	
	public function testScenario000148() {
		$this->assertOutput("scenario000148", Module::LISTS, Module::IMAGES, Module::CODE );
	}
	
	public function testScenario000149() {
		$this->assertOutput("scenario000149", Module::PARAGRAPHS, Module::LISTS, Module::IMAGES, Module::CODE );
	}
	
	public function testScenario000150() {
		$this->assertOutput("scenario000150", Module::HEADINGS, Module::LISTS, Module::IMAGES, Module::CODE );
	}
	
	public function testScenario000151() {
		$this->assertOutput("scenario000151", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::IMAGES, Module::CODE );
	}
	
	public function testScenario000152() {
		$this->assertOutput("scenario000152", Module::LINKS, Module::IMAGES, Module::CODE );
	}
	
	public function testScenario000153() {
		$this->assertOutput("scenario000153", Module::PARAGRAPHS, Module::LINKS, Module::IMAGES, Module::CODE );
	}
	
	public function testScenario000154() {
		$this->assertOutput("scenario000154", Module::HEADINGS, Module::LINKS, Module::IMAGES, Module::CODE );
	}
	
	public function testScenario000155() {
		$this->assertOutput("scenario000155", Module::PARAGRAPHS, Module::HEADINGS, Module::LINKS, Module::IMAGES, Module::CODE );
	}
	
	public function testScenario000156() {
		$this->assertOutput("scenario000156", Module::LISTS, Module::LINKS, Module::IMAGES, Module::CODE );
	}
	
	public function testScenario000157() {
		$this->assertOutput("scenario000157", Module::PARAGRAPHS, Module::LISTS, Module::LINKS, Module::IMAGES, Module::CODE );
	}
	
	public function testScenario000158() {
		$this->assertOutput("scenario000158", Module::HEADINGS, Module::LISTS, Module::LINKS, Module::IMAGES, Module::CODE );
	}
	
	public function testScenario000159() {
		$this->assertOutput("scenario000159", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::LINKS, Module::IMAGES, Module::CODE );
	}
	
	public function testScenario000160() {
		$this->assertOutput("scenario000160", Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000161() {
		$this->assertOutput("scenario000161", Module::PARAGRAPHS, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000162() {
		$this->assertOutput("scenario000162", Module::HEADINGS, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000163() {
		$this->assertOutput("scenario000163", Module::PARAGRAPHS, Module::HEADINGS, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000164() {
		$this->assertOutput("scenario000164", Module::LISTS, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000165() {
		$this->assertOutput("scenario000165", Module::PARAGRAPHS, Module::LISTS, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000166() {
		$this->assertOutput("scenario000166", Module::HEADINGS, Module::LISTS, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000167() {
		$this->assertOutput("scenario000167", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000168() {
		$this->assertOutput("scenario000168", Module::LINKS, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000169() {
		$this->assertOutput("scenario000169", Module::PARAGRAPHS, Module::LINKS, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000170() {
		$this->assertOutput("scenario000170", Module::HEADINGS, Module::LINKS, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000171() {
		$this->assertOutput("scenario000171", Module::PARAGRAPHS, Module::HEADINGS, Module::LINKS, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000172() {
		$this->assertOutput("scenario000172", Module::LISTS, Module::LINKS, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000173() {
		$this->assertOutput("scenario000173", Module::PARAGRAPHS, Module::LISTS, Module::LINKS, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000174() {
		$this->assertOutput("scenario000174", Module::HEADINGS, Module::LISTS, Module::LINKS, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000175() {
		$this->assertOutput("scenario000175", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::LINKS, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000176() {
		$this->assertOutput("scenario000176", Module::IMAGES, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000177() {
		$this->assertOutput("scenario000177", Module::PARAGRAPHS, Module::IMAGES, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000178() {
		$this->assertOutput("scenario000178", Module::HEADINGS, Module::IMAGES, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000179() {
		$this->assertOutput("scenario000179", Module::PARAGRAPHS, Module::HEADINGS, Module::IMAGES, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000180() {
		$this->assertOutput("scenario000180", Module::LISTS, Module::IMAGES, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000181() {
		$this->assertOutput("scenario000181", Module::PARAGRAPHS, Module::LISTS, Module::IMAGES, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000182() {
		$this->assertOutput("scenario000182", Module::HEADINGS, Module::LISTS, Module::IMAGES, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000183() {
		$this->assertOutput("scenario000183", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::IMAGES, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000184() {
		$this->assertOutput("scenario000184", Module::LINKS, Module::IMAGES, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000185() {
		$this->assertOutput("scenario000185", Module::PARAGRAPHS, Module::LINKS, Module::IMAGES, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000186() {
		$this->assertOutput("scenario000186", Module::HEADINGS, Module::LINKS, Module::IMAGES, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000187() {
		$this->assertOutput("scenario000187", Module::PARAGRAPHS, Module::HEADINGS, Module::LINKS, Module::IMAGES, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000188() {
		$this->assertOutput("scenario000188", Module::LISTS, Module::LINKS, Module::IMAGES, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000189() {
		$this->assertOutput("scenario000189", Module::PARAGRAPHS, Module::LISTS, Module::LINKS, Module::IMAGES, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000190() {
		$this->assertOutput("scenario000190", Module::HEADINGS, Module::LISTS, Module::LINKS, Module::IMAGES, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000191() {
		$this->assertOutput("scenario000191", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::LINKS, Module::IMAGES, Module::FORMATTING, Module::CODE );
	}
	
	public function testScenario000192() {
		$this->assertOutput("scenario000192", Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000193() {
		$this->assertOutput("scenario000193", Module::PARAGRAPHS, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000194() {
		$this->assertOutput("scenario000194", Module::HEADINGS, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000195() {
		$this->assertOutput("scenario000195", Module::PARAGRAPHS, Module::HEADINGS, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000196() {
		$this->assertOutput("scenario000196", Module::LISTS, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000197() {
		$this->assertOutput("scenario000197", Module::PARAGRAPHS, Module::LISTS, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000198() {
		$this->assertOutput("scenario000198", Module::HEADINGS, Module::LISTS, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000199() {
		$this->assertOutput("scenario000199", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000200() {
		$this->assertOutput("scenario000200", Module::LINKS, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000201() {
		$this->assertOutput("scenario000201", Module::PARAGRAPHS, Module::LINKS, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000202() {
		$this->assertOutput("scenario000202", Module::HEADINGS, Module::LINKS, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000203() {
		$this->assertOutput("scenario000203", Module::PARAGRAPHS, Module::HEADINGS, Module::LINKS, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000204() {
		$this->assertOutput("scenario000204", Module::LISTS, Module::LINKS, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000205() {
		$this->assertOutput("scenario000205", Module::PARAGRAPHS, Module::LISTS, Module::LINKS, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000206() {
		$this->assertOutput("scenario000206", Module::HEADINGS, Module::LISTS, Module::LINKS, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000207() {
		$this->assertOutput("scenario000207", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::LINKS, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000208() {
		$this->assertOutput("scenario000208", Module::IMAGES, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000209() {
		$this->assertOutput("scenario000209", Module::PARAGRAPHS, Module::IMAGES, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000210() {
		$this->assertOutput("scenario000210", Module::HEADINGS, Module::IMAGES, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000211() {
		$this->assertOutput("scenario000211", Module::PARAGRAPHS, Module::HEADINGS, Module::IMAGES, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000212() {
		$this->assertOutput("scenario000212", Module::LISTS, Module::IMAGES, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000213() {
		$this->assertOutput("scenario000213", Module::PARAGRAPHS, Module::LISTS, Module::IMAGES, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000214() {
		$this->assertOutput("scenario000214", Module::HEADINGS, Module::LISTS, Module::IMAGES, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000215() {
		$this->assertOutput("scenario000215", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::IMAGES, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000216() {
		$this->assertOutput("scenario000216", Module::LINKS, Module::IMAGES, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000217() {
		$this->assertOutput("scenario000217", Module::PARAGRAPHS, Module::LINKS, Module::IMAGES, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000218() {
		$this->assertOutput("scenario000218", Module::HEADINGS, Module::LINKS, Module::IMAGES, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000219() {
		$this->assertOutput("scenario000219", Module::PARAGRAPHS, Module::HEADINGS, Module::LINKS, Module::IMAGES, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000220() {
		$this->assertOutput("scenario000220", Module::LISTS, Module::LINKS, Module::IMAGES, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000221() {
		$this->assertOutput("scenario000221", Module::PARAGRAPHS, Module::LISTS, Module::LINKS, Module::IMAGES, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000222() {
		$this->assertOutput("scenario000222", Module::HEADINGS, Module::LISTS, Module::LINKS, Module::IMAGES, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000223() {
		$this->assertOutput("scenario000223", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::LINKS, Module::IMAGES, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000224() {
		$this->assertOutput("scenario000224", Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000225() {
		$this->assertOutput("scenario000225", Module::PARAGRAPHS, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000226() {
		$this->assertOutput("scenario000226", Module::HEADINGS, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000227() {
		$this->assertOutput("scenario000227", Module::PARAGRAPHS, Module::HEADINGS, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000228() {
		$this->assertOutput("scenario000228", Module::LISTS, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000229() {
		$this->assertOutput("scenario000229", Module::PARAGRAPHS, Module::LISTS, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000230() {
		$this->assertOutput("scenario000230", Module::HEADINGS, Module::LISTS, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000231() {
		$this->assertOutput("scenario000231", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000232() {
		$this->assertOutput("scenario000232", Module::LINKS, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000233() {
		$this->assertOutput("scenario000233", Module::PARAGRAPHS, Module::LINKS, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000234() {
		$this->assertOutput("scenario000234", Module::HEADINGS, Module::LINKS, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000235() {
		$this->assertOutput("scenario000235", Module::PARAGRAPHS, Module::HEADINGS, Module::LINKS, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000236() {
		$this->assertOutput("scenario000236", Module::LISTS, Module::LINKS, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000237() {
		$this->assertOutput("scenario000237", Module::PARAGRAPHS, Module::LISTS, Module::LINKS, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000238() {
		$this->assertOutput("scenario000238", Module::HEADINGS, Module::LISTS, Module::LINKS, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000239() {
		$this->assertOutput("scenario000239", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::LINKS, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000240() {
		$this->assertOutput("scenario000240", Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000241() {
		$this->assertOutput("scenario000241", Module::PARAGRAPHS, Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000242() {
		$this->assertOutput("scenario000242", Module::HEADINGS, Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000243() {
		$this->assertOutput("scenario000243", Module::PARAGRAPHS, Module::HEADINGS, Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000244() {
		$this->assertOutput("scenario000244", Module::LISTS, Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000245() {
		$this->assertOutput("scenario000245", Module::PARAGRAPHS, Module::LISTS, Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000246() {
		$this->assertOutput("scenario000246", Module::HEADINGS, Module::LISTS, Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000247() {
		$this->assertOutput("scenario000247", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000248() {
		$this->assertOutput("scenario000248", Module::LINKS, Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000249() {
		$this->assertOutput("scenario000249", Module::PARAGRAPHS, Module::LINKS, Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000250() {
		$this->assertOutput("scenario000250", Module::HEADINGS, Module::LINKS, Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000251() {
		$this->assertOutput("scenario000251", Module::PARAGRAPHS, Module::HEADINGS, Module::LINKS, Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000252() {
		$this->assertOutput("scenario000252", Module::LISTS, Module::LINKS, Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000253() {
		$this->assertOutput("scenario000253", Module::PARAGRAPHS, Module::LISTS, Module::LINKS, Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000254() {
		$this->assertOutput("scenario000254", Module::HEADINGS, Module::LISTS, Module::LINKS, Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	public function testScenario000255() {
		$this->assertOutput("scenario000255", Module::PARAGRAPHS, Module::HEADINGS, Module::LISTS, Module::LINKS, Module::IMAGES, Module::FORMATTING, Module::BLOCKQUOTES, Module::CODE );
	}
	
	private function assertOutput($file) {
		$html = file_get_contents ( dirname ( __FILE__ ) . '/testsuite/_e2e/html5/' . $file . '.htm' );
		$html = mb_convert_encoding ( $html, 'UTF-8', mb_detect_encoding ( $html, 'UTF-8, ISO-8859-1', true ) );
		
		$parser = new Parser ();
		$parser->setModules ( array_slice ( func_get_args (), 1 ) );
		$document = $parser->parseFile ( dirname ( __FILE__ ) . '/testsuite/_e2e/koara/e2e.kd' );
		$renderer = new Html5Renderer ();
		$document->accept ( $renderer );
		
		$this->assertEquals ( $html, $renderer->getOutput () );
	}
}
