<?php

namespace Iulyanp\Sitemap;

use DateTime;
use XMLWriter;

/**
 * Class XmlSitemapGenerator
 */
class XmlSitemapGenerator implements SitemapGeneratorInterface
{
    const EXT = '.xml';
    const SCHEMA = 'http://www.sitemaps.org/schemas/sitemap/0.9';
    const SCHEMA_INSTANCE = 'http://www.w3.org/2001/XMLSchema-instance';
    const XHTML_PROTOCOL = 'http://www.w3.org/1999/xhtml';
    const IMAGE_PROTOCOL = 'http://www.google.com/schemas/sitemap-image/1.1';
    const SCHEMA_SITEMAP = 'sitemap.xsd';
    const SCHEMA_SITEMAP_INDEX = 'siteindex.xsd';
    const ITEM_PER_SITEMAP = 50000;
    const SITEMAP_MAX_SIZE = 9;
    const SEPARATOR = '_';
    const INDEX_SUFFIX = 'index';
    /**
     * @var \XMLWriter
     */
    private $writer;
    /**
     * @var SitemapConfig
     */
    private $sitemapConfig;
    /**
     * @var int
     */
    private $currentItem = 0;
    /**
     * @var int
     */
    private $currentSitemap = 0;

    /**
     *
     * @param SitemapConfig $sitemapConfig
     */
    public function __construct(SitemapConfig $sitemapConfig)
    {
        $this->sitemapConfig = $sitemapConfig;
    }

    /**
     * Assigns XMLWriter object instance
     *
     * @param \XMLWriter $writer
     */
    private function setWriter(XMLWriter $writer)
    {
        $this->writer = $writer;
    }

    /**
     * Adds an item to sitemap
     *
     * @param string $loc
     * @param string $priority
     * @param string $changefreq
     * @param string $lastmod
     *
     * @return SitemapGenerator
     */
    public function addItem(
        string $loc,
        string $priority = self::DEFAULT_PRIORITY,
        string $changefreq = self::DEFAULT_CHANGEFREQ,
        string $lastmod = self::DEFAULT_LASTMOD
    ) {

        $this->startSitemap();
        $this->writer->startElement('url');
        $this->writer->writeElement('loc', $this->getWebLink($loc));
        $this->writer->writeElement('priority', $priority);
        $this->writer->writeElement('changefreq', $changefreq);
        $this->writer->writeElement('lastmod', $this->getLastModifiedDate($lastmod));

        $this->addOptionalTags($loc);

        $this->writer->endElement();
        $this->incrementCurrentItem();
    }

    /**
     * Creates Google index sitemap
     */
    public function createSitemapIndex()
    {
        if ($this->writer) {
            $this->endSitemap(); // close the last generated sitemap @Todo find another way to do this
        }

        $this->startSitemapIndex();

        for ($index = 0; $index <= $this->getCurrentSitemap(); $index++) {
            $indexLoc = $this->getFilename($index);
            $this->addIndexItem($indexLoc);
        }

        $this->endSitemap();
    }

    /**
     * Start sitemap
     */
    private function startSitemap()
    {
        if (!$this->isLimitReached()) {
            return;
        }

        if ($this->writer) {
            $this->endSitemap();
            $this->incCurrentSitemap();
            $this->resetCurrentItem();
        }

        $this->setWriter(new XMLWriter());
        $this->writer->openURI($this->getCurrentFilePath());
        $this->writer->startDocument('1.0', 'UTF-8');
        $this->writer->setIndent(true);
        $this->writer->startElement('urlset');
        $this->addHeaderAttributesValidation(self::SCHEMA_SITEMAP);
    }

    /**
     * Start index sitemap
     */
    private function startSitemapIndex()
    {
        $this->setWriter(new XMLWriter());
        $this->writer->openURI($this->getFilePath(self::INDEX_SUFFIX));
        $this->writer->startDocument('1.0', 'UTF-8');
        $this->writer->setIndent(true);
        $this->writer->startElement('sitemapindex');
        $this->addHeaderAttributesValidation(self::SCHEMA_SITEMAP_INDEX);
    }

    /**
     * Close the sitemap file
     */
    private function endSitemap()
    {
        $this->writer->endDocument();
    }

    /**
     * @param string $loc
     */
    private function addIndexItem(string $loc)
    {
        $this->writer->startElement('sitemap');
        $this->writer->writeElement('loc',
            $this->getWebLink(sprintf('%s/%s', $this->sitemapConfig->getBaseUrl(), $loc))
        );
        $this->writer->writeElement('lastmod', $this->getLastModifiedDate());
        $this->writer->endElement();
    }

    /**
     * @param string $type
     */
    private function addHeaderAttributesValidation(string $type)
    {
        $this->writer->writeAttribute('xmlns', self::SCHEMA);
        $this->writer->writeAttribute('xmlns:xsi', self::SCHEMA_INSTANCE);
        $this->writer->writeAttribute('xsi:schemaLocation', sprintf('%s %s/%s', self::SCHEMA, self::SCHEMA, $type));
        $this->writer->writeAttribute('xmlns:xhtml', self::XHTML_PROTOCOL);
        $this->writer->writeAttribute('xmlns:image', self::IMAGE_PROTOCOL);
    }

    /**
     * @param string $loc
     */
    private function addOptionalTags(string $loc)
    {
        if (!$this->sitemapConfig->getMobileDomain()) {
            return;
        }
        $this->writer->startElement('xhtml:link');
        $this->writer->setIndent(true);
        $this->writer->writeAttribute('rel', 'alternate');
        $this->writer->writeAttribute('media', 'only screen and (max-width: 640px)');
        $this->writer->writeAttribute('href', $this->getMobileLink($loc));
        $this->writer->endElement(); //end element xhtml:link
    }

    /**
     * @param $index
     *
     * @return string
     */
    private function getFilename($index): string
    {
        return sprintf('%s%s%s%s', $this->sitemapConfig->getFilename(), self::SEPARATOR, $index, self::EXT);
    }

    /**
     * @return int
     */
    private function getCurrentItem(): int
    {
        return $this->currentItem;
    }

    /**
     * @return $this
     */
    private function resetCurrentItem()
    {
        $this->currentItem = 0;

        return $this;
    }

    /**
     * @return $this
     */
    private function incrementCurrentItem()
    {
        $this->currentItem = $this->currentItem + 1;

        return $this;
    }

    /**
     * @return int
     */
    private function getCurrentSitemap(): int
    {
        return $this->currentSitemap;
    }

    /**
     * @return $this
     */
    private function incCurrentSitemap()
    {
        $this->currentSitemap = $this->currentSitemap + 1;

        return $this;
    }

    /**
     * @param string $relativeLink
     *
     * @return string
     */
    private function getWebLink(string $relativeLink): string
    {
        return sprintf('%s/%s', $this->sitemapConfig->getWebDomain(), $this->encodeUrl($relativeLink));
    }

    /**
     * @param string $relativeLink
     *
     * @return string
     */
    private function getMobileLink(string $relativeLink): string
    {
        return sprintf('%s/%s', $this->sitemapConfig->getMobileDomain(), $this->encodeUrl($relativeLink));
    }

    /**
     * @return string
     */
    private function getCurrentFilePath(): string
    {
        return $this->getFilePath($this->getCurrentSitemap());
    }

    /**
     * @param $fileIndex
     *
     * @return string
     */
    private function getFilePath($fileIndex): string
    {
        return sprintf('%s%s%s', $this->sitemapConfig->getPath(), DIRECTORY_SEPARATOR, $this->getFilename($fileIndex));
    }

    /**
     * @return float
     */
    private function getCurrentSitemapSize()
    {
        return ceil(filesize($this->getCurrentFilePath()) / 1024 / 1024);
    }

    /**
     * @param string $date
     *
     * @return string
     */
    private function getLastModifiedDate(string $date = self::DEFAULT_LASTMOD): string
    {
        return (new DateTime($date))->format('Y-m-d');
    }

    /**
     * @return bool
     */
    private function isLimitReached(): bool
    {
        return ($this->getCurrentItem() % self::ITEM_PER_SITEMAP) == 0
            || $this->getCurrentSitemapSize() > self::SITEMAP_MAX_SIZE;
    }

    /**
     * @param string $relativeLink
     *
     * @return string
     */
    private function encodeUrl(string $relativeLink): string
    {
        $relativeLink = htmlentities(trim($relativeLink, '/'), ENT_COMPAT, 'UTF-8');

        $relativeLink = implode('/', array_map('urlencode', explode('/', $relativeLink)));

        return $relativeLink;
    }
}
