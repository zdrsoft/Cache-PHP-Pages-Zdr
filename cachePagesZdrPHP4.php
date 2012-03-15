<?php
/*
* Writen By Zdravko Shishmanov
* email: zdrsoft@gmail.com
* 01.2011
*/

// Cache directory - define full path (with slash at the end)
define(CACHE_DIRECTORY, '/home/gamesch/public_html/ru/cache_tmp/');
// set in seconds
define(CACHING_TIME_SECONDS, 1286400);
// set in seconds // Interval for clearing of garbage cache files
define(CLEAR_OLD_CACHE_FILES_TIME_SECONDS, 11113600);

//==============================================================================
class CachePagesZDR
{
    var $cacheTime;
    var $cacheFile;
    
    //==========================================================================
    function CachePagesZDR() {
        $this->initCacheSettings();
        
        if($this->cacheFileNeedUpdate($this->cacheFile, $this->cacheTime)) {
            // start the output buffer
            ob_start();       
        } else {
            $this->printCachedPage();
        }
    }

    //==========================================================================
    function pageFooter() {
        $pageContent = ob_get_contents();
        $this->writeFile($this->cacheFile, $pageContent);
        ob_end_flush();
        $this->clearOldCacheFiles();
    }

    //==========================================================================
    function initCacheSettings() {
        $tmp = CACHE_DIRECTORY . $_SERVER["REQUEST_URI"];
        if(strstr($tmp,'?')) {
            $tmp = explode('?', $tmp);
            $tmp = $tmp[0];
        }
        $tmp = explode('/', $tmp);
        $tmp = end($tmp);
        
        $this->cacheFile = $this->initCachefILE();       
        $this->cacheTime = CACHING_TIME_SECONDS;
    }

    //==========================================================================
    function initCachefILE() {
        $tmpString = '';
        $tmp = CACHE_DIRECTORY . $_SERVER["REQUEST_URI"];
        if(strstr($tmp,'?')) {
            $tmp = explode('?', $tmp);
            $tmp = $tmp[0];
        }
        $tmp = explode('/', $tmp);
        $tmp = end($tmp);
        
        foreach($_GET as $key => $value) { 
            $tmpString .= $key.$value;
        }
        
        foreach($_POST as $key => $value) { 
            $tmpString .= $key.substr($value, 0, 10);
        }
        
        $cacheFileTmp = CACHE_DIRECTORY . $tmp . '_' . md5($tmpString) . '_cache';
        
        return  $cacheFileTmp;
    }

    //==========================================================================
    function clearOldCacheFiles() {
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
    function printCachedPage() {
        include($this->cacheFile);
        exit;
    }

    //==========================================================================
    function cacheFileNeedUpdate($filePath, $timeInterval) {
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
    function writeFile($filePath, $content) {
        $fp = fopen($filePath, 'w'); 
        fwrite($fp, $content); 
        fclose($fp);
    }
}

$cacheZDR = new CachePagesZDR();

?>
