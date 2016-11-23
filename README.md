# Micropage sandbox
Very simple micropage sandbox based on nette framework.  Modify templates, pages config file, upload files on hosting and you're done. 

Only non-standard features are covered by this readme. For more information about Nette framework, syntax of latte (templates) and neon (configuration) see official documentation here: https://doc.nette.org/en/2.4/

Demo at: http://microsandbox.cmcode.cz


----------


## Development installation
**Prerequisites**
**Required:** 
Composer: https://getcomposer.org/

**Optional:**
Node.js NPM: https://nodejs.org/en/ 
Sass - Compass: http://compass-style.org/
Grunt: http://gruntjs.com/

 1. Clone this repository
 2. Inside cloned folder run command: `composer install`
 3. Inside cloned folder run command: `npm install`
 4. Build js and css files by command: `grunt default`


----------


## App structure
Main structure is based on standart nette 2.4 framework project, only difference is in app/config folder, where config files for (optinal) database connection and pages are separated from main config.neon.
#### Presenters (/app/presenters)
WebPresenter - Main presenter with only one render action (renderDefault), responsible for rendering all pages.
SitemapPresenter - Presenter which renders sitemap.xml and robots.txt
#### Models (/app/model)
Web - all backend functions needed for basic functionality, all functions are documented in code.
#### Views (/app/presenters/templates)
**All templates uses latte template system (standard in Nette framework). https://latte.nette.org/en/** 

**layout** - Main site layout, embeds default blocks: 

 - content - page content
 - desc - meta description tag content
 - title - title tag content
 - scripts - placed right before end of body tag
 - head - placed right before end of head tag

**page templates** - every template must contain at least block content. Default location for template files is folder app/presenters/templates/Web/pages, only exception is homepage template at location app/presenters/templates/Web/default.latte
**sitemap** - app/presenters/templates/Sitemap/default.latte
**robots** - app/presenters/templates/Sitemap/robots.latte

#### Configuration files (/app/config)
**All configuration files uses neon syntax (standard in Nette framework). https://ne-on.org/**

**pages.neon** - config which contains all data about pages (more info further)
**config.neon** - standard register of services, factories etc.
**database.config.neon** - optional database config, located in examples

#### Components (/app/components)
This folder contains subfolders with available components, their templates, config files etc. All config files are automatically loaded. (see app/boostrap.php).

At this time, there are only one component - contact form, which allows saving of submissions in file or database and sending emails to admin. See code for more info.

###pages.neon explanation

    parameters:
	version: '20160916'
	siteName: 'Micropage Sandbox'
	pages: {
		1: { #key is id of page
			'slug': 'test-page', #url slug
			'title': 'Test page', #page title tag
			'desc': 'This is microsandbox test page', #page meta desc tag
			'template': 'test-page', #name of template file without extension, can be also path relative to default pages template folder 
			'menuItem': 'Link to test page', #Text of link in navigation, can be null etc, if page is not in nav
			'navigation': 'main', #identification of navigation, can be null
			'menuOrder': 1, #order in navigation from low to high
			'customParams': { #any variable name: any value(service, etc)  
				'sampleCustomParameter': 'This is my content :)',
				'anotherParameter': 'Any content can be passed'
			}
	}
	


----------


##Default frontend
All frontend-related files are located in www folder. This repo includes some most common libs as Bootstrap v3.3.6, jQuery, nette.ajax.js etc. You can use it as boilerplate, or not.



