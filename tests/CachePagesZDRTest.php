<?php
require_once(dirname(__FILE__).'/../CachePagesZDR.php');
require_once(dirname(__FILE__).'/CachePagesZDRWrapper.php');

// Support PHPUnit <=3.7 and >=3.8
if (@include_once('PHPUnit/Framework/TestCase.php')===false) // <= 3.7
	require_once('src/Framework/TestCase.php'); // >= 3.8

class CachePagesZDRTest extends PHPUnit_Framework_TestCase {
	
	private $cachePagesZdr;
	
	public function setUp(){
		
		$_SERVER["REQUEST_URI"] = 'page_test.html';
		$this->cachePagesZdr = new CachePagesZDRWrapper();
	}
	
	public function testInitCacheSettings() {
		
		$this->assertEquals(true, $this->cachePagesZdr->initCacheSettingsCall());	

		$cacheFilePath = $this->cachePagesZdr->initCacheFileCall();
		$this->assertEquals($cacheFilePath, $this->cachePagesZdr->getCacheFileName());
	}
	
	public function testInitCacheFile() {
		
		$cacheFilePath = $this->cachePagesZdr->initCacheFileCall();
		$strstrRes = strstr($cacheFilePath, 'd41d8cd98f00b204e9800998ecf8427e_cache');
		
		$this->assertNotEquals(false, $strstrRes);
		$this->assertEquals('d41d8cd98f00b204e9800998ecf8427e_cache', $strstrRes);
	}
	
	public function testIsWriteableCacheDirectory() {
		
		$this->assertEquals(true, is_writable(CACHE_DIRECTORY));
	}
	
	public function testCacheFileNeedUpdate() {
		
		$cacheFilePath = $this->cachePagesZdr->initCacheFileCall();
		$doNeedUpdate = $this->cachePagesZdr->cacheFileNeedUpdateCall($cacheFilePath, 1);
		if ($doNeedUpdate) {
			$this->cachePagesZdr->pageCacheStart();
			echo "\nThis is test page cached content!\n";
			$this->cachePagesZdr->pageCacheEnd();
		}
		
		$doNeedUpdate = $this->cachePagesZdr->cacheFileNeedUpdateCall($cacheFilePath, 999999);
		$this->assertEquals(false, $doNeedUpdate);
		
		$cachedFileContent = file_get_contents($cacheFilePath);
		$this->assertEquals("\nThis is test page cached content!\n", $cachedFileContent);
	}
	
	public function testClearOldCacheFiles() {
		
		$tmpDirectoryPath = CACHE_DIRECTORY . 'test_directory_path/';	
		$tmpDirectoryOldFileForCleaning = $tmpDirectoryPath.'clearCacheFileTimeFile.txt';
		
		if (!file_exists($tmpDirectoryPath)) {
			$this->assertEquals(true, mkdir($tmpDirectoryPath, 0777));			
			$this->assertEquals(true, $this->cachePagesZdr->writeFileCall($tmpDirectoryOldFileForCleaning, " 1 "));
		}
		
		$this->assertEquals(0, $this->cachePagesZdr->clearOldCacheFilesCall($tmpDirectoryPath, 0));		
		$this->assertEquals(true, unlink($tmpDirectoryOldFileForCleaning));
		$this->assertEquals(true, unlink(CACHE_DIRECTORY . "page_test.html_d41d8cd98f00b204e9800998ecf8427e_cache"));
		$this->assertEquals(true, rmdir($tmpDirectoryPath));
	}
}
?>