### Sitemap Generator

Sitemap Generator is a lightweight PHP class that can generate sitemaps.

#### Installation

```
$ composer require iulyanp/sitemap-generator
```

#### How it works

The XmlSitemapGenerator class can generate Google sitemaps in xml formats.

```php
<?php

use Iulyanp\Sitemap\SitemapConfig;
use Iulyanp\Sitemap\XmlSitemapGenerator;

$config = new SitemapConfig('http://www.google.com', 'http://m.google.com/');
$sitemapGenerator = new XmlSitemapGenerator($config);

// generate some static links
$sitemapGenerator->addItem('/', '1.0', 'daily', 'now');
$sitemapGenerator->addItem('/about', '0.4', 'yearly', 'Jul 08');
$sitemapGenerator->addItem('/contact', '0.7', 'monthly', '11-11-2009');

// create the index sitemap for all generated sitemaps
$sitemapGenerator->createSitemapIndex();
```

The SitemapGenerator needs an instance of SitemapConfig which takes as it's first argument the `webDomain`
and optionally as the second argument the `mobileDomain`.
When you specify the second argument all the sitemap items will contain also a mobile element. 

Also you could specify:
- the `path` where the sitemaps will be saved with `$config->setPath('/sitemaps/')
- the `filename` of the generated sitemaps `$config->setFilename('sitemap')`
- the `baseUrl` with `$config->setBaseUrl('sitemap')` eg. `http://host.domain/{baseUrl}/{filename}`

```php
<?php

require_once './vendor/autoload.php';

use Iulyanp\Sitemap\SitemapConfig;
use Iulyanp\Sitemap\SitemapGeneratorInterface;
use Iulyanp\Sitemap\XmlSitemapGenerator;

class AppSitemap
{
    private $sitemapGenerator;

    public function __construct(SitemapGeneratorInterface $sitemapGenerator)
    {
        $this->sitemapGenerator = $sitemapGenerator;
    }
    
    public function generate()
    {
        // generate some static links
        $this->sitemapGenerator->addItem('/', '1.0', 'daily', 'now');
        $this->sitemapGenerator->addItem('/about', '0.8', 'monthly', 'Jun 25');
        $this->sitemapGenerator->addItem('/contact', '0.6', 'yearly', '14-12-2009');

        // create a DB query and dynamically generate the links
        for ($i = 0; $i <= 100000; $i++) {
            $this->sitemapGenerator->addItem('/products/' . $i . '/');
        }

        // create the index sitemap for all generated sitemaps
        $this->sitemapGenerator->createSitemapIndex();
    }
}

$config = new SitemapConfig('http://www.google.com', 'http://m.google.com/');
$config
    ->setPath('/testings/')
    ->setFilename('test')
    ->setBaseUrl('testing');
$sitemapGenerator = new XmlSitemapGenerator($config);

$app = new AppSitemap($sitemapGenerator);
$app->generate();
```

You can also add extra tags on each item from your sitemap if you extend XmlSitemapGenerator and overwrite the 
`addOptionalTags` method.

