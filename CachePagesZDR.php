<?php
/*
* Writen By Zdravko Shishmanov
* email: zdrsoft {@} gmail.com
* Last update: 25.03.2015
*/

// Cache directory - define full path (with slash at the end)
define('CACHE_DIRECTORY', __DIR__ . '/tmp/');
// set in seconds
define('CACHING_TIME_SECONDS', 5);
// set in seconds // Interval for clearing of garbage cache files
define('CLEAR_OLD_CACHE_FILES_TIME_SECONDS', 604800); // One week 604800 seconds

//==============================================================================
class CachePagesZDR
{
    protected $cacheTime;
    protected $cacheFile;
    
    //==========================================================================
    public function pageCacheStart() {
        $this->initCacheSettings();
        
        if($this->cacheFileNeedUpdate($this->cacheFile, $this->cacheTime)) {
            // start the output buffer
            ob_start();       
        } else {
            $this->printCachedPage();
        }
    }

    //==========================================================================
    public function pageCacheEnd() {
        $pageContent = ob_get_contents();
        $this->writeFile($this->cacheFile, $pageContent);
        ob_end_flush();
        $this->clearOldCacheFiles(CACHE_DIRECTORY, CLEAR_OLD_CACHE_FILES_TIME_SECONDS);
    }

    //==========================================================================
    protected function initCacheSettings() {
    	if (!is_writable(CACHE_DIRECTORY)) {
    		echo 'Please make cache directory '.CACHE_DIRECTORY.' writeable.';
			return false;
    	}
    	
        $this->cacheFile = $this->initCacheFile();       
        $this->cacheTime = CACHING_TIME_SECONDS;
        
        return true;
    }

    //==========================================================================
    protected function initCacheFile() {
        $tmp = CACHE_DIRECTORY . $_SERVER["REQUEST_URI"];
        if(strstr($tmp,'?')) {
            $tmp = explode('?', $tmp);
            $tmp = $tmp[0];
        }
        $tmp = explode('/', $tmp);
        $cacheFileName = end($tmp);

        $tmpString = '';
        foreach($_GET as $key => $value) { 
            $tmpString .= $key.$value;
        }
        
        foreach($_POST as $key => $value) { 
            $tmpString .= $key.substr($value, 0, 10);
        }
        
        $cacheFileNameRes = CACHE_DIRECTORY . 
        					$cacheFileName  . 
        								'_' . 
        					md5($tmpString) . 
        							'_cache';
        
        return  $cacheFileNameRes;
    }

    //==========================================================================
    protected function clearOldCacheFiles($cacheDirectory, $timeInterval) {
    	$result = false;
        $timeClearCacheFile = $cacheDirectory . 'clearCacheFileTimeFile.txt';
        if($this->cacheFileNeedUpdate($timeClearCacheFile, 
                                        $timeInterval)) {
            $dir = dir($cacheDirectory);
            while (false !== ($entry = $dir->read())) {
                if($entry=='.' || $entry=='..') continue;
                  
                $fileNameTmp =  $cacheDirectory . $entry;
                unlink($fileNameTmp);
            }
            
            $dir->close();
            $result = $this->writeFile($timeClearCacheFile, '');
        }
        
        return $result;
    }

    //==========================================================================
    private function printCachedPage() {
        include($this->cacheFile);
        exit;
    }

    //==========================================================================
    protected function cacheFileNeedUpdate($filePath, $timeInterval) {
        if (!file_exists($filePath)) {
            return true;
        }

        if ((time() - $timeInterval) < filemtime($filePath)) {
            return false;
        } else {
            return true;
        }
    }

    //==========================================================================
    protected function writeFile($filePath, $content) {
        $fp = fopen($filePath, 'w'); 
        $res = fwrite($fp, $content); 
        fclose($fp);
        
        return $res;
    }
}
?>