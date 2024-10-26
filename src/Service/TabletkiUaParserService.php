<?php

namespace App\Service;

use App\DTO\ProductDTO;
use Symfony\Component\DomCrawler\Crawler;

readonly class TabletkiUaParserService implements ProductParserInterface
{
    private const DOMAIN = 'https://tabletki.ua';

    public function supports(string $url): bool
    {
        return preg_match('#^(https://)?tabletki.ua/uk/category/\d+#', $url);
    }

    public function parse(string $content): array
    {
        $crawler = new Crawler($content);

        $products = [];

        $crawler->filterXPath('//article[contains(@class, "card__category")]')->each(
            function (Crawler $node) use (&$products) {
                $name = $node->filterXPath('.//div[contains(@class,"card__category--info")]/a/span')->text();
                $url = $node->filterXPath('.//a')->attr('href');
                $image = $node->filterXPath('.//img')->attr('src');
                $price = $node->filterXPath('.//div[contains(@class,"card__category--price")]/span')->text();
                $products[] = new ProductDTO(
                    $name,
                    $this->normalizePrice($price),
                    $this->normalizeUrl($url),
                    $this->normalizeUrl($image)
                );
            }
        );

        return $products;
    }

    private function normalizePrice(string $priceText): string
    {
        return preg_replace('/[^\d.]/', '', $priceText);
    }

    private function normalizeUrl(string $url): string
    {
        if (!preg_match('#^https?://#', $url)) {
            $url = self::DOMAIN . '/' . ltrim($url, '/');
        }
        return $url;
    }
}
