<?php

namespace App\DTO;

class ProductDTO
{
    private ?string $name = null;

    private ?float $price = null;

    private ?string $productUrl = null;

    private ?string $imageUrl = null;

    public function __construct(?string $name, ?float $price, ?string $productUrl, ?string $imageUrl)
    {
        $this->name = $name;
        $this->price = $price;
        $this->productUrl = $productUrl;
        $this->imageUrl = $imageUrl;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function getProductUrl(): ?string
    {
        return $this->productUrl;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }
}