<?php

require_once(dirname(__FILE__).'/../CachePagesZDR.php');

class CachePagesZDRWrapper extends CachePagesZDR {

	public function initCacheSettingsCall() {
		return $this->initCacheSettings();
	}

	public function initCacheFileCall() {
		return $this->initCacheFile();
	}
	
	public function getCacheFileName() {
		return $this->cacheFile;
	}
	
	public function cacheFileNeedUpdateCall($filePath, $timeInterval) {
		return $this->cacheFileNeedUpdate($filePath, $timeInterval);
	}
	
	public function writeFileCall($path, $content) {
		$this->writeFile($path, $content);
	}
	
	public function clearOldCacheFilesCall($cacheDirectory, $timeInterval) {
		return $this->clearOldCacheFiles($cacheDirectory, $timeInterval);
	}
}

?>