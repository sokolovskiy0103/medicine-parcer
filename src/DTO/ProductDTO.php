<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
class ProductDTO
{
    #[Assert\NotBlank(message: 'Product name cannot be empty')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Product name cannot be longer than {{ limit }} characters'
    )]
    private ?string $name = null;

    #[Assert\NotBlank(message: 'Price cannot be empty')]
    #[Assert\Regex(
        pattern: '/^\d+(\.\d{1,2})?$/',
        message: 'Price must be a valid number with up to 2 decimal places'
    )]
    private ?string $price = null;

    #[Assert\Url(message: 'Invalid product URL')]
    private ?string $productUrl = null;

    #[Assert\Url(message: 'Invalid image URL')]
    private ?string $imageUrl = null;

    public function __construct(?string $name, ?string $price, ?string $productUrl, ?string $imageUrl)
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
        return (float) $this->price;
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