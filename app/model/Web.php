<?php

namespace App\Model;

use Nette;

class Web extends Nette\Object {

    private $version;
    private $pageConfig;
    private $siteName;

    const MAIN_NAVIGATION = 'main';
    const GALLERY_PATH = '/../../www/img/gallery/';
    const GALLERY_URI = '/img/gallery/';
    
    public function __construct($version, $siteName, $pageConfig) {
        $this->version = $version;
        $this->pageConfig = $pageConfig;
        $this->siteName = $siteName;
    }

    /**
     * Gets version number from configuration
     * @return string
     */
    public function getVersion() {
        return $this->version;
    }
    
    /**
     * Gets site name from configuration
     * @return string
     */
    public function getSiteName(){
        return $this->siteName;
    }
    
    /**
     * Get sorted pages for navigation
     * @param string $navigationName
     * @return array
     */
    public function getNavigation($navigationName){
        $result = [];
        $sort = [];
        $temp = [];
        foreach($this->pageConfig as $id=>$page){
            if($page['navigation'] == $navigationName){
                $temp[$id] = $page;
                $sort[$id] = $page['menuOrder'];
            }
        }
        if(!empty($temp)){
            asort($sort);
            foreach($sort as $id=>$pageOrder){
                $result[$id] = $temp[$id];
            }
        }
        return $result;
    }
    
    public function pageExists($pageId){
        return key_exists($pageId, $this->pageConfig);
    }
    
    public function getPageTemplate($pageId){
        if($this->pageExists($pageId) && file_exists(__DIR__.'/../presenters/templates/Web/pages/'. $this->pageConfig[$pageId]['template'].'.latte')){
            return __DIR__.'/../presenters/templates/Web/pages/'. $this->pageConfig[$pageId]['template'].'.latte';
        } else {
            throw new Nette\Application\BadRequestException('Non-existent page template.');
        }
    }
    
    public function getCustomParams($pageId){
        if($this->pageExists($pageId)){
            return $this->pageConfig[$pageId]['customParams'];
        }
        return [];
    }
    
    public static function getGalleryImages(){
        $images = \Nette\Utils\Finder::findFiles('*.jpg')->from(__DIR__.self::GALLERY_PATH.'thumbs/');
        $result = [];
        if(!empty($images)){
            foreach($images as $spl){
                $filename = $spl->getBasename();
                if(file_exists(__DIR__.self::GALLERY_PATH.$filename)){
                    $result[self::GALLERY_URI.'thumbs/'.$filename] = self::GALLERY_URI.$filename;
                }
            }
        }
        return $result;
    }
    
    public function getMenuText($pageId){
        if($this->pageExists($pageId)){
            return $this->pageConfig[$pageId]['menuItem'];
        }
        return false;
    }
    
    public static function getPageSlugs(array $pageConfig){
        $result = [];
        foreach($pageConfig as $pageId=>$pageData){
            $result[$pageId] = $pageData['slug'];
        }
        return $result;
    }
    
    public function getPageIds(){
        return array_keys($this->pageConfig);
    }
}
