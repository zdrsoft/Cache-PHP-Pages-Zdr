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
define('CLEAR_OLD_CACHE_FILES_TIME_SECONDS', 91111111);

//==============================================================================
class CachePagesZDR
{
    private $cacheTime;
    private $cacheFile;
    
    //==========================================================================
    public function __construct() {
        $this->initCacheSettings();
        
        if($this->cacheFileNeedUpdate($this->cacheFile, $this->cacheTime)) {
            // start the output buffer
            ob_start();       
        } else {
            $this->printCachedPage();
        }
    }

    //==========================================================================
    public function pageFooter() {
        $pageContent = ob_get_contents();
        $this->writeFile($this->cacheFile, $pageContent);
        ob_end_flush();
        $this->clearOldCacheFiles();
    }

    //==========================================================================
    protected function initCacheSettings() {
    	if (!is_writable(CACHE_DIRECTORY)) {
    		echo 'Please make cache directory '.CACHE_DIRECTORY.' writeable.';
			exit;
    	}
    	
        $this->cacheFile = $this->initCacheFile();       
        $this->cacheTime = CACHING_TIME_SECONDS;
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
        
        $cacheFileTmp = CACHE_DIRECTORY . $cacheFileName . '_' . md5($tmpString) . '_cache';
        
        return  $cacheFileTmp;
    }

    //==========================================================================
    protected function clearOldCacheFiles() {
        $timeClearCacheFile = CACHE_DIRECTORY . 'clearCacheFileTimeFile.txt';
        if($this->cacheFileNeedUpdate($timeClearCacheFile, 
                                        CLEAR_OLD_CACHE_FILES_TIME_SECONDS)) {
            $dir = dir(CACHE_DIRECTORY);
            while (false !== ($entry = $dir->read())) {
                if($entry=='.' || $entry=='..') continue;
                  
                $fileNameTmp =  CACHE_DIRECTORY . $entry;
                unlink($fileNameTmp);
            }
            
            $dir->close();
            $this->writeFile($timeClearCacheFile, '');
        }
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
        fwrite($fp, $content); 
        fclose($fp);
    }
}

$cacheZDR = new CachePagesZDR();
?>