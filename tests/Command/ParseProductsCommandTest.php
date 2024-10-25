<?php

namespace App\Tests\Command;

use App\Command\ParseProductsCommand;
use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\ProductParserService;
use App\DTO\ProductDTO;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Serializer\SerializerInterface;

class ParseProductsCommandTest extends TestCase
{
    private ProductParserService $parserService;
    private SerializerInterface $serializer;
    private CommandTester $commandTester;
    private EntityRepository $productRepository;

    protected function setUp(): void
    {
        $this->parserService = $this->createMock(ProductParserService::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->productRepository = $this->createMock(ProductRepository::class);

        $entityManager
            ->method('getRepository')
            ->with(Product::class)
            ->willReturn($this->productRepository);

        $command = new ParseProductsCommand(
            $this->parserService,
            $entityManager,
            $this->serializer
        );

        $this->commandTester = new CommandTester($command);
    }

    public function testSuccessfulExecution(): void
    {
        $url = 'https://example.com';
        $csvPath = sys_get_temp_dir() . '/test.csv';
        $products = [
            $this->createProductDTO('Product 1', 10.99),
            $this->createProductDTO('Product 2', 20.99),
        ];
        $csvContent = 'name,price,imageUrl,productUrl\n"Product 1",10.99,...,...';

        $this->parserService->expects($this->once())
            ->method('parse')
            ->with($url)
            ->willReturn($products);

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($products, 'csv')
            ->willReturn($csvContent);

        $this->productRepository->expects($this->exactly(2))
            ->method('findOrCreateProduct')
            ->willReturn( (new Product()));

        $this->commandTester->execute([
            'url' => $url,
            'path' => $csvPath,
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('Parsed 2 items successfully', $this->commandTester->getDisplay());
        $this->assertStringContainsString('Parsing finished successfully!', $this->commandTester->getDisplay());
    }

    public function testExecutionWithParsingError(): void
    {
        $url = 'https://example.com';

        $this->parserService->expects($this->once())
            ->method('parse')
            ->with($url)
            ->willThrowException(new \RuntimeException('Failed to parse URL'));

        $this->commandTester->execute([
            'url' => $url,
        ]);

        $this->assertEquals(1, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('An error occurred: Failed to parse URL', $this->commandTester->getDisplay());
    }

    public function testExecutionWithInvalidWritePath(): void
    {
        $url = 'https://example.com';
        $invalidPath = '/invalid/path/file.csv';
        $products = [$this->createProductDTO('Product 1', 10.99)];

        $this->parserService->expects($this->once())
            ->method('parse')
            ->willReturn($products);

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->willReturn('csv content');

        $this->commandTester->execute([
            'url' => $url,
            'path' => $invalidPath,
        ]);

        $this->assertEquals(1, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('Path', $this->commandTester->getDisplay());
        $this->assertStringContainsString('is not writable', $this->commandTester->getDisplay());
    }

    public function testAutoAppendCsvExtension(): void
    {
        $url = 'https://example.com';
        $path = sys_get_temp_dir() . '/test';
        $products = [$this->createProductDTO('Product 1', 10.99)];

        $this->parserService->expects($this->once())
            ->method('parse')
            ->willReturn($products);

        $this->serializer->expects($this->once())
            ->method('serialize')
            ->willReturn('csv content');

        $this->commandTester->execute([
            'url' => $url,
            'path' => $path,
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
        $this->assertStringContainsString($path . '.csv', $this->commandTester->getDisplay());
    }

    private function createProductDTO(string $name, float $price): ProductDTO
    {
        return new ProductDTO(
            $name,
            $price,
            'https://example.com/image.jpg',
            'https://example.com/product');
    }
}