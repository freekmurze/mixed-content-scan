<?php

namespace Spatie\MixedContentScanner;

use Spatie\Crawler\Url;
use Spatie\Crawler\Crawler;
use Spatie\Crawler\CrawlProfile;
use Spatie\Crawler\CrawlInternalUrls;
use Spatie\MixedContentScanner\Exceptions\InvalidUrl;

class MixedContentScanner
{
    /** @var \Spatie\MixedContentScanner\MixedContentObserver */
    public $mixedContentObserver;

    /** @var null|\Spatie\Crawler\CrawlProfile */
    public $crawlProfile;

    /** @var int|null */
    protected $maximumCrawlCount;

    public function __construct(MixedContentObserver $mixedContentObserver)
    {
        $this->mixedContentObserver = $mixedContentObserver;
    }

    public function scan(string $url, array $clientOptions = [])
    {
        $this->guardAgainstInvalidUrl($url);

        $url = Url::create($url);

        $crawler = Crawler::create($clientOptions);

        if ($this->maximumCrawlCount) {
            $crawler->setMaximumCrawlCount($this->maximumCrawlCount);
        }

        $crawler->setCrawlProfile($this->crawlProfile ?? new CrawlInternalUrls($url))
            ->setCrawlObserver($this->mixedContentObserver)
            ->startCrawling($url);
    }

    public function setCrawlProfile(CrawlProfile $crawlProfile)
    {
        $this->crawlProfile = $crawlProfile;

        return $this;
    }

    public function setMaximumCrawlCount(int $maximumCrawlCount)
    {
        $this->maximumCrawlCount = $maximumCrawlCount;

        return $this;
    }

    protected function guardAgainstInvalidUrl(string $url)
    {
        if ($url == '') {
            throw InvalidUrl::urlIsEmpty();
        }

        if (! $this->startsWith($url, ['http://', 'https://'])) {
            throw InvalidUrl::invalidScheme($url);
        }
    }

    protected function startsWith(string $haystack, array $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ($needle != '' && substr($haystack, 0, strlen($needle)) === (string) $needle) {
                return true;
            }
        }

        return false;
    }
}
