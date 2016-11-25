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
    public function getSiteName() {
        return $this->siteName;
    }

    /**
     * Get sorted pages for navigation
     * @param string $navigationName
     * @return array
     */
    public function getNavigation($navigationName) {
        $result = [];
        $sort = [];
        $temp = [];
        foreach ($this->pageConfig as $id => $page) {
            if ($page['navigation'] == $navigationName) {
                $temp[$id] = $page;
                $sort[$id] = $page['menuOrder'];
            }
        }
        if (!empty($temp)) {
            asort($sort);
            foreach ($sort as $id => $pageOrder) {
                $result[$id] = $temp[$id];
            }
        }
        return $result;
    }

    /**
     * Exists page in config?
     * @param int $pageId
     * @return bool
     */
    public function pageExists($pageId) {
        return key_exists($pageId, $this->pageConfig);
    }

    /**
     * Gets page template file path
     * @param int $pageId
     * @return string
     * @throws Nette\Application\BadRequestException
     */
    public function getPageTemplate($pageId) {
        if ($this->pageExists($pageId) && file_exists(__DIR__ . '/../presenters/templates/Web/pages/' . $this->pageConfig[$pageId]['template'] . '.latte')) {
            return __DIR__ . '/../presenters/templates/Web/pages/' . $this->pageConfig[$pageId]['template'] . '.latte';
        } else {
            throw new Nette\Application\BadRequestException('Non-existent page template.');
        }
    }

    /**
     * Returns array with page custom parameters defined in config
     * @param int $pageId
     * @return array
     */
    public function getCustomParams($pageId) {
        if ($this->pageExists($pageId)) {
            return $this->pageConfig[$pageId]['customParams'];
        }
        return [];
    }

     /**
     * Get array of thumb=>image in gallery folder
     * Scans folder for jpegs
     * @param string $subfolder
     * @return array
     */
    public static function getGalleryImages($subfolder = null) {
        $images = \Nette\Utils\Finder::findFiles('*.jpg')->from(__DIR__ . self::GALLERY_PATH .(($subfolder!==null) ? ($subfolder.'/') : ''). 'thumbs/');
        $result = [];
        if (!empty($images)) {
            foreach ($images as $spl) {
                $filename = $spl->getBasename();
                if (file_exists(__DIR__ . self::GALLERY_PATH.(($subfolder!==null) ? ($subfolder.'/') : '') . $filename)) {
                    $result[self::GALLERY_URI .(($subfolder!==null) ? ($subfolder.'/') : ''). 'thumbs/' . $filename] = self::GALLERY_URI.(($subfolder!==null) ? ($subfolder.'/') : '') . $filename;
                }
            }
        }
        return $result;
    }

    /**
     * Get menu item text
     * @param int $pageId
     * @return string || bool false
     */
    public function getMenuText($pageId) {
        if ($this->pageExists($pageId)) {
            return $this->pageConfig[$pageId]['menuItem'];
        }
        return false;
    }

    /**
     * Returns array of available slugs
     * @param array $pageConfig
     * @return array
     */
    public static function getPageSlugs(array $pageConfig) {
        $result = [];
        foreach ($pageConfig as $pageId => $pageData) {
            $result[$pageId] = $pageData['slug'];
        }
        return $result;
    }

    /**
     * Return array of page ids
     * @return array
     */
    public function getPageIds() {
        return array_keys($this->pageConfig);
    }

    /**
     * Get whole page config section as array
     * @param int $pageId
     * @return array
     */
    public function getPageData($pageId) {
        return (key_exists($pageId, $this->pageConfig)) ? $this->pageConfig[$pageId] : [];
    }

}
