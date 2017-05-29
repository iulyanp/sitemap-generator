<?php

namespace Iulyanp\Sitemap;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class SitemapConfig
{
    private $webDomain;
    private $mobileDomain;
    private $path = 'sitemaps';
    private $baseUrl = 'sitemap';
    private $filename = 'sitemap';

    /**
     * SitemapConfig constructor.
     *
     * @param $webDomain
     * @param $mobileDomain
     */
    public function __construct($webDomain, $mobileDomain = '')
    {
        $this->webDomain = trim($webDomain, '/');
        $this->mobileDomain = trim($mobileDomain, '/');
    }

    /**
     * @return mixed
     */
    public function getWebDomain()
    {
        return $this->webDomain;
    }

    /**
     * @return string
     */
    public function getMobileDomain(): string
    {
        return $this->mobileDomain;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     *
     * @return SitemapConfig
     */
    public function setPath($path)
    {
        $this->makeDir($path);

        $this->path = trim($path, '/');

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param mixed $baseUrl
     *
     * @return SitemapConfig
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * @param mixed $filename
     *
     * @return SitemapConfig
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    private function makeDir($path)
    {
        $fs = new Filesystem();

        try {
            $fs->mkdir(sprintf('%s%s%s', realpath(__DIR__), DIRECTORY_SEPARATOR, $path));
        } catch (IOExceptionInterface $e) {
            echo "An error occurred while creating your directory at " . $e->getPath();
        }
    }
}
