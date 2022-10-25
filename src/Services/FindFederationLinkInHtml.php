<?php

namespace App\Services;

use Psr\Log\LoggerInterface;

/**
 * Cette classe recherche des liens vers les flux
 * atom / rss dans le contenu d'une page html.
 */
class FindFederationLinkInHtml
{

    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * 
     * 
     * @param {string} html content
     * @return {array} matches
     */
    public function find($url, $html)
    {

        $this->logger->debug('Start searching for rss/atom links');

        $found = [];

        preg_match_all('/<[^>]*type="application\/(rss|atom)\+xml"[^>]*>/m', $html, $matches);

        if (count($matches) > 0) {
            $found = array_map(function ($it) {
                preg_match('/href="([^"]+)"/', $it, $submatches);
                if (empty($submatches)) return;
                return $submatches[1];
            }, $matches[0]);
        }

        if (empty($found)) {
            $infos = parse_url($url);
            preg_match_all('/<a.*href="(https?:\/\/' . str_replace('.', '\\.', $infos['host']) . '[^"]+(rss|atom)[^"]*)".*>(.*)<\/a>/', $html, $matches);

            if (count($matches) > 0) {
                $found = $matches[1];
            }
        }

        $found = array_unique($found);
        return $found;
    }
}
