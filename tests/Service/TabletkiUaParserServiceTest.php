<?php

namespace App\Tests\Service;

use App\DTO\ProductDTO;
use App\Service\TabletkiUaParserService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TabletkiUaParserServiceTest extends TestCase
{
    private TabletkiUaParserService $parser;

    protected function setUp(): void
    {
        $this->parser = new TabletkiUaParserService();
    }

    #[DataProvider('urlProvider')]
    public function testSupports(string $url, bool $expected): void
    {
        $this->assertEquals($expected, $this->parser->supports($url));
    }

    public static  function urlProvider(): array
    {
        return [
            'valid url with https' => ['https://tabletki.ua/uk/category/123', true],
            'valid url without https' => ['tabletki.ua/uk/category/456', true],
            'invalid url' => ['https://example.com/uk/category/123', false],
            'invalid path' => ['https://tabletki.ua/uk/products/123', false],
            'invalid subdomain' => ['https://shop.tabletki.ua/uk/category/123', false],
        ];
    }

    public function testParse(): void
    {
        $html = <<<EOF
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Test Page</title>
</head>
<body>
<div class="category-products">
    <!-- Normal product card -->
    <article class="card__category">
        <div class="card__category--image">
            <a href="/uk/product/paracetamol-500">
                <img src="/images/products/paracetamol.jpg" alt="Парацетамол">
            </a>
        </div>
        <div class="card__category--info">
            <a href="/uk/product/paracetamol-500">
                <span>Парацетамол 500 мг</span>
            </a>
        </div>
        <div class="card__category--price">
            <span>123.45 ₴</span>
        </div>
    </article>

    <!-- Product card with different price format -->
    <article class="card__category">
        <div class="card__category--image">
            <a href="/uk/product/aspirin">
                <img src="/images/products/aspirin.jpg" alt="Аспірин">
            </a>
        </div>
        <div class="card__category--info">
            <a href="/uk/product/aspirin">
                <span>Аспірин кардіо 100 мг</span>
            </a>
        </div>
        <div class="card__category--price">
            <span>1 234.56 грн</span>
        </div>
    </article>

    <!-- Product card with absolute URLs -->
    <article class="card__category">
        <div class="card__category--image">
            <a href="https://tabletki.ua/uk/product/nurofen">
                <img src="https://tabletki.ua/images/products/nurofen.jpg" alt="Нурофен">
            </a>
        </div>
        <div class="card__category--info">
            <a href="/uk/product/nurofen">
                <span>Нурофен форте 400 мг</span>
            </a>
        </div>
        <div class="card__category--price">
            <span>99.99</span>
        </div>
    </article>
</div>
</body>
</html>
EOF;

        $products = $this->parser->parse($html);

        $this->assertCount(3, $products, 'Parser should extract exactly 3 products from HTML');
        $this->assertContainsOnlyInstancesOf(ProductDTO::class, $products);

        $firstProduct = $products[0];
        $this->assertEquals('Парацетамол 500 мг', $firstProduct->getName());
        $this->assertEquals(123.45, $firstProduct->getPrice());
        $this->assertEquals('https://tabletki.ua/uk/product/paracetamol-500', $firstProduct->getProductUrl());
        $this->assertEquals('https://tabletki.ua/images/products/paracetamol.jpg', $firstProduct->getImageUrl());

        $secondProduct = $products[1];
        $this->assertEquals('Аспірин кардіо 100 мг', $secondProduct->getName());
        $this->assertEquals(1234.56, $secondProduct->getPrice());
        $this->assertEquals('https://tabletki.ua/uk/product/aspirin', $secondProduct->getProductUrl());
        $this->assertEquals('https://tabletki.ua/images/products/aspirin.jpg', $secondProduct->getImageUrl());

        $thirdProduct = $products[2];
        $this->assertEquals('Нурофен форте 400 мг', $thirdProduct->getName());
        $this->assertEquals(99.99, $thirdProduct->getPrice());
        $this->assertEquals('https://tabletki.ua/uk/product/nurofen', $thirdProduct->getProductUrl());
        $this->assertEquals('https://tabletki.ua/images/products/nurofen.jpg', $thirdProduct->getImageUrl());
    }

    public function testParseEmptyContent(): void
    {
        $products = $this->parser->parse('<html><body></body></html>');
        $this->assertEmpty($products, 'Parser should return empty array for HTML without products');
    }

    public function testParseInvalidHtml(): void
    {
        $invalidHtml = '<<<invalid html>>>';
        $products = $this->parser->parse($invalidHtml);
        $this->assertEmpty($products);
    }
}