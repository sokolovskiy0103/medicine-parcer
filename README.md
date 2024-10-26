# Medicine Parser Application

A Symfony-based application for parsing product information from e-commerce websites. Currently supports parsing products from Tabletki.ua.

## Features

- Command-line interface for product parsing
- Saves parsed data to both CSV file and database

## Requirements

- PHP 8.2 or higher
- Symfony 7.1 or higher
- Composer
- MySQL/MariaDB

## Installation

1. Clone the repository:
```bash
git clone git@github.com:sokolovskiy0103/medicine-parcer.git
cd medicine-parcer
```

2. Configure your database in `.env`:
```env
DATABASE_URL="mysql://user:password@127.0.0.1:3306/product_parser"
```

3. Install dependencies:
```bash
composer install
```


## Usage

### Parse Products Command

The main command for parsing products:

```bash
php bin/console app:parse-products [url] [csv-path]
```

Arguments:
- `url`: (Required) URL to parse products from
- `csv-path`: (Optional) Path where to save the CSV file. If not specified, a temporary file will be created

Examples:
```bash
php bin/console app:parse-products https://tabletki.ua/uk/category/242/ ./products.csv
php bin/console app:parse-products https://tabletki.ua/uk/category/2060/filter/page=2/
```

### Supported URLs

Currently, the application supports parsing from:
- Tabletki.ua category pages (format: `https://tabletki.ua/uk/category/{id}`)


## Testing

The project includes unit tests for core components. To run the tests:

```bash
php bin/phpunit
```

## Adding New Parsers

To add support for a new website:

1. Create a new parser service implementing `ProductParserInterface`

Example parser implementation:
```php
class NewWebsiteParserService implements ProductParserInterface
{
    public function supports(string $url): bool
    {
        // Add URL validation logic
    }

    public function parse(string $content): array
    {
        // Add parsing logic
        return $products;
    }
}
```



