<?php

class ADBT_Controller_Site extends ADBT_Controller_Base
{

    public function home()
    {
        $this->view->title = 'Welcome';
        $this->view->output();
    }

    public function css($media = 'all.css')
    {
        $css = $this->view->getStyle($media);

        switch ($media) {
            case 'screen.css':
                $this->view->outputCssScreen();
        }
    }

    public function resources($type = false, $file = false)
    {
        // Ensure a leading slash
        if (substr($file, 0, 1) != '/') {
            $file = "/$file";
        }
        // Look for the file in Local
        $filepaths = array(
            Config::$path_to_local . '/resources/' . $type . '/' . $file,
            Config::$base_path . '/resources/' . $type . '/' . $file,
        );
        foreach ($filepaths as $filepath) {
            if (file_exists($filepath)) {
                $path = realpath($filepath);
                switch ($type) {
                    case 'css':
                        $mime = 'text/css';
                        break;
                    case 'js':
                        $mime = 'text/javascript';
                        break;
                    default:
                        $mime = $this->mime($path);
                }
                header("Content-type:$mime");
                exit(file_get_contents($path));
            }
        }
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
