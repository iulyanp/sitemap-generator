<?php

namespace Iulyanp\Sitemap;

interface SitemapGeneratorInterface
{
    const DEFAULT_PRIORITY = '0.5';
    const DEFAULT_CHANGEFREQ = 'weekly';
    const DEFAULT_LASTMOD = 'now';

    public function addItem(
        string $loc,
        string $priority = self::DEFAULT_PRIORITY,
        string $changefreq = self::DEFAULT_CHANGEFREQ,
        string $lastmod = self::DEFAULT_LASTMOD
    );

    public function createSitemapIndex();
}
