<?php

/**
 * RssMaker - basic RSS feed generator for Pico
 */
final class RssMaker extends AbstractPicoPlugin
{
    private $giveFeed = false;  // boolean to determine if the user has typed example.com/feed
    private $feedTitle = '';    // title of the feed will be the site title
    private $baseURL = '';      // this will hold the base url
    /**
     * This plugin is enabled by default?
     *
     * @see AbstractPicoPlugin::$enabled
     * @var boolean
     */
    protected $enabled = false;

    /**
     * This plugin depends on ...
     *
     * @see AbstractPicoPlugin::$dependsOn
     * @var string[]
     */
    protected $dependsOn = array();

    /**
     * Triggered after Pico has loaded all available plugins
     *
     * This event is triggered whether the plugin is enabled or not.
     * It is NOT guaranteed that plugin dependencies are fulfilled!
     *
     * @see    Pico::getPlugin()
     * @see    Pico::getPlugins()
     * @param  object[] &$plugins loaded plugin instances
     * @return void
     */

    public function onConfigLoaded(array &$config)
    {
        // Get site data

        $this->feedTitle = $config['site_title'];
        $this->baseURL = $config['base_url'];
    }

    /**
     * Triggered after Pico has evaluated the request URL
     *
     * @see    Pico::getRequestUrl()
     * @param  string &$url part of the URL describing the requested contents
     * @return void
     */
    public function onRequestUrl(&$url)
    {
        // If example.com/feed, then true
        if ($url == 'feed') {
            $this->giveFeed = true;
        }
    }


    public function onPagesLoaded(
        array &$pages,
        array &$currentPage = null,
        array &$previousPage = null,
        array &$nextPage = null
    )
    {
        // If this is the feed link, return RSS feed
        if ($this->giveFeed) {
            //Sitemap found, 200 OK
            header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
            header("Content-Type: application/xml; charset=UTF-8");

            //RSS Start
            $rss = '<?xml version="1.0" encoding="utf-8"?>';
            $rss .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
            $rss .= '<channel><title>';
            $rss .= $this->feedTitle;
            $rss .= '</title>';
            $rss .= '<atom:link href="' . $this->baseURL . 'feed" rel="self" />';

            $rss .= '<image>';
            $rss .= '<url>' . $this->baseURL . '../watershed.ico</url>';
            $rss .= '<title>A Watershed Walk</title>';
            $rss .= '<link>' . $this->baseURL . '</link>';
            $rss .= '</image>';

            function sortByPubDate($a, $b)
            {
                return $b['time'] - $a['time'];
            }

            usort($pages, "sortByPubDate");

            //Page loop
            foreach ($pages as $page) {
                if (!empty($page['date'])) {
                    $rss .= '<item>';
                    $rss .= '<title>';
                    $rss .= $page['title'];
                    $rss .= '</title>';

                    $rss .= '<description>';
                    $rss .= htmlspecialchars($page['content']);
                    $rss .= '</description>';

                    $rss .= '<link>';
                    $rss .= $page['url'];
                    $rss .= '</link>';

                    $rss .= '<pubDate>';
                    $rss .= date('c', $page['time']);
                    $rss .= '</pubDate>';

                    $rss .= '<guid>';
                    $rss .= $page['url'];
                    $rss .= '</guid>';

                    $rss .= '</item>';
                }


            }
            //RSS End
            $rss .= '</channel></rss>';
            //Show generated sitemap
            die($rss);

        }

    }
}
