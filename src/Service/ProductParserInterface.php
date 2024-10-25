<?php

namespace App\Service;

use App\DTO\ProductDTO;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.productParser')]
interface ProductParserInterface
{
    public function supports(string $url): bool;

    /**
     * @return ProductDTO[]
     */
    public function parse(string $content): array;
}