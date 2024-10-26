<?php

namespace App\Command;

use App\Entity\Product;
use App\Exception\ValidationException;
use App\Service\ProductParserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

#[AsCommand(
    name: 'app:parse-products',
    description: 'Parse products from a specified URL, save to CSV and database'
)]
class ParseProductsCommand extends Command
{
    public function __construct(
        private readonly ProductParserService $parserService,
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('url', InputArgument::REQUIRED, 'URL to parse')
            ->addArgument('path', InputArgument::OPTIONAL, 'Path to save CSV file (temporary file by default)');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $url = $input->getArgument('url');
        $path = $input->getArgument('path') ?? tempnam(sys_get_temp_dir(), 'csv');
        if (pathinfo($path, PATHINFO_EXTENSION) !== 'csv') {
            $path .= '.csv';
        }

        $io->writeln("Starting parsing for URL: $url");

        try {
            $products = $this->parserService->parse($url);
            $productCount = count($products);
            $io->writeln("Parsed $productCount items successfully");

            $io->writeln('Saving parsed products to CSV file...');
            $csvData = $this->serializer->serialize($products, 'csv');

            if (!is_writable(dirname($path))) {
                $io->error("Path '$path' is not writable.");
                return Command::FAILURE;
            }

            file_put_contents($path, $csvData);
            $io->writeln("CSV file saved to: $path");

            $io->writeln('Saving products to the database...');
            $this->saveProductsToDatabase($products, $io);

            $io->success('Parsing finished successfully!');
            return Command::SUCCESS;
        } catch (ValidationException $e) {
            $io->error('Validation failed:');
            foreach ($e->getErrors() as $path => $messages) {
                foreach ($messages as $message) {
                    $io->writeln(sprintf('  - %s: %s', $path, $message));
                }
            }
        } catch (Throwable $e) {
            $io->error('An error occurred: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function saveProductsToDatabase(array $products): void
    {
        foreach ($products as $productDTO) {
            $product = $this->entityManager
                ->getRepository(Product::class)
                ->findOrCreateProduct($productDTO->getName());
            $product->setPrice($productDTO->getPrice())
                ->setImageUrl($productDTO->getImageUrl())
                ->setProductUrl($productDTO->getProductUrl());
            if ($product->getId() === null){
                $this->entityManager->persist($product);
            }
        }
        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}
