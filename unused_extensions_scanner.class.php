<?php

/**
 * Extension Scanner Class
 * 
 * Class incapsulates logic of management php extensions.
 * 
 * @author Levin Pavel
 * @version 0.1
 */

class ExtensionsScanner
{
    public $skip_extensions = array('core', 'xdebug', 'eAccelerator', 'apc');
    

    /**
     * Returns formatting extensions names that should be skipped
     * 
     * @return array
     */
    private function getSkipExtensions()
    {
        return array_map('strtolower', array_map('trim', $this->skip_extensions));
    }
    
    
    /**
     * Looks for loaded, but unused php extenstion in files of indicated directory.
     * 
     * This can be useful if you want to speed up your scripts.
     * To do this, disable unused extensions in the configuration file of PHP.
     * Some extensions may not be called out of the code  but they may perfom service functions (xDebug, eAccelerator, APC, etc)
     * Check the list of extensions before  switching off
     *
     * Caution: The author does not guarantee 100% accuracy of the scanner work
     * Make sure your applications work after you disable extensions.
     * 
     * What extension is used, can be defined as follows:
     *   - with the reflection of the extension are extracted the names of classes, functions and constants
     *   - these names are searched in files of the indicated directory
     * 
     * @param string $path Directory of php project(s)
     * @return array Names of unused extensions
     */
    public function findUnusedExts($path)
    {
        // Validate path
        if (!file_exists($path) || !is_dir($path))
        {
            throw new Exception("Directory $path does not exists or is not a directory");
        }
        
        $exts = get_loaded_extensions();
        $exts_keywords = array();

        // Extract keywords from the php-extensions (names of classes, functions and constants)
        foreach ($exts as $ext)
        {
            if (in_array(strtolower($ext), $this->getSkipExtensions()))
            {
                continue;
            }

            $reflect = new ReflectionExtension($ext);

            $keywords = array_merge($reflect->getClassNames(), array_keys($reflect->getConstants()), array_keys($reflect->getFunctions()));

            if (!is_array($keywords) || count($keywords) == 0)
            {
                continue;
            }

            $exts_keywords[$ext] = $keywords;
        }

        // List files from project directory
        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($objects as $object)
        {
            if ($object->isFile())
            {
                $ext = pathinfo($object, PATHINFO_EXTENSION);

                if ($ext == 'php')
                {
                    //echo "Processing of $object\n";

                    // Find for keywords in the file
                    $temp = file_get_contents($object);

                    foreach ($exts_keywords as $ext=>$keywords)
                    {
                        foreach ($keywords as $keyword)
                        {
                            if (stripos($temp, $keyword) !== FALSE)
                            {
                                // Keyword found - this extension is used
                                //echo "Extension $ext is used\n";
                                unset($exts_keywords[$ext]);
                                break;
                            }
                        }
                    }

                    if (count($exts_keywords) == 0)
                    {
                        break;
                    }
                }
            }
        }

        return array_keys($exts_keywords);
    }
}