<?php

class Projects {

    protected $_directory;
    protected $_colCount = 3;


    public function __construct(){
        $this->_directory = realpath(dirname(__FILE__) . '/../../../') . '/';
    }

    public function getDirectoryFromSiteName($siteName = null){
        if (!$siteName) {
            return false;
        }
        if (is_dir($this->_directory . $siteName . '/webroot')) {
            return $this->_directory . $siteName . '/webroot/';
        }
        return $this->_directory . $siteName . '/';
    }


    public function getProjectsArray($dir = null){

        $dirArray = array();
        if(!$dir){
            $dir = $this->_directory;
        }

        $files = glob($dir . "*");
        foreach($files as $file)
        {
            if(is_dir($file)){$dirArray[] = $file;}
        }

        return $dirArray;
    }

    public function renderProjects(){
        $projectsDirectoryArray = $this->getProjectsArray();

        $content = '';

        $i = 0;
        $mod = 0;
        foreach($projectsDirectoryArray as $project){
            $content .= $this->beautifyProject($project);
        }

        return $content;
    }

    public function beautifyProject($dirName = null){
        $html = '';
        if($dirName){
            if(strripos($dirName, '/')){
                $siteName = substr($dirName, strripos($dirName, '/') + 1);
                $html .= "<option value='./index.php?a=debug&site=$siteName'>$siteName</option>";

            }
        }
        return $html;
    }
}
