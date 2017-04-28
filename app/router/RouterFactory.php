<?php

namespace App;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;


class RouterFactory
{
	use Nette\StaticClass;

        private $slugs;
        private $ids;

        public function __construct($pages) {
            $this->slugs = Model\Web::getPageSlugs($pages);
            $this->ids = array_flip($this->slugs);
        }
        
        /**
	 * @return Nette\Application\IRouter
	 */
	public function createRouter()
	{
                $router = new RouteList;
                $router[] = new Route('sitemap[.xml]', 'Sitemap:default');
                $router[] = new Route('robots[.txt]', 'Sitemap:robots');
                $router[] = new Route('<id>', [
                    'presenter'=>'Web',
                    'action'=>'default',
                    'id'=>[
                        Route::VALUE => null,
                        Route::FILTER_IN => [$this, 'filterIn'],
                        Route::FILTER_OUT => [$this, 'filterOut'],
                        Route::FILTER_STRICT => true
                    ]
                ]);
		$router[] = new Route('<presenter>/<action>[/<id>]', 'Web:default');
		return $router;
	}
        
        public function filterIn($slug){
            return key_exists($slug, $this->ids) ? $this->ids[$slug] : null;
        }
        
        public function filterOut($id){
            return key_exists($id, $this->slugs) ? $this->slugs[$id] : null;
        }

}
