<?php

class ADBT_Controller_Site extends ADBT_Controller_Base
{

    protected $name = 'Site';

    public function home()
    {
        $this->view->title = 'Welcome';
        $this->view->output();
    }

    /**
     * 
     * @param type $type
     * @param type $file
     */
    public function resources($type = false, $file = false)
    {
        if (!$type && !$file) exit(0);

        // Ensure a leading slash
        if (substr($file, 0, 1) != '/') {
            $file = "/$file";
        }

        // Set the mime type and send the file
        $paths = explode(PATH_SEPARATOR, get_include_path());
        $out = '';
        foreach ($paths as $path) {
            $filepath = $path.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.$type.$file;
            if (file_exists($filepath)) {
                $path = realpath($filepath);

                // CSS and JS get concatenated
                if ($type=='css' || $type=='js') {
                    if ($type=='js') $type = 'javascript';
                    header("Content-type:text/$type");
                    $out .= file_get_contents($path);
                } else {
                    // Other files get output immediately.
                    $mime = $this->mime($path);
                    header("Content-type:$mime");
                    echo file_get_contents($path);
                    exit(0);
                }
            }
        }

        // Output JS and CSS
        echo $out;
        exit(0);
    }

    /**
     * From Kohana.
     * 
     * @param type $filename
     * @return boolean
     */
    public function mime($filename)
    {
        // Get the complete path to the file
        $filename = realpath($filename);

        // Get the extension from the filename
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // For images
        if (preg_match('/^(?:jpe?g|png|[gt]if|bmp|swf)$/', $extension)) {
            // Use getimagesize() to find the mime type on images
            $file = getimagesize($filename);
            if (isset($file['mime']))
                return $file['mime'];
        }

        if (class_exists('finfo', FALSE)) {
            $info = new finfo(defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME);
            if ($info) {
                return $info->file($filename);
            }
        }

        if (ini_get('mime_magic.magicfile') AND function_exists('mime_content_type')) {
            // The mime_content_type function is only useful with a magic file
            return mime_content_type($filename);
        }

        /* if (!empty($extension)) {
          return File::mime_by_ext($extension);
          } */

        // Unable to find the mime-type
        return FALSE;
    }

}
