<?php

namespace App\Service;

use App\DTO\ProductDTO;
use App\Exception\ParserNotSupportedException;
use App\Exception\ValidationException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
readonly class ProductParserService
{
    /**
     * @param ProductParserInterface[] $parsers
     */
    public function __construct(
        #[AutowireIterator('app.productParser')]
        private iterable $parsers,
        private HttpClientInterface $client,
        private ValidatorInterface $validator,
    ) {}

    /**
     * @return ProductDTO[]
     * @throws ParserNotSupportedException
     * @throws TransportExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function parse(string $url): array
    {
        foreach ($this->parsers as $parser) {
            if ($parser->supports($url)) {
                $request = $this->client->request('GET', $url);
                $products =  $parser->parse($request->getContent());
                $violations = $this->validator->validate($products);
                if (count($violations) > 0) {
                    throw new ValidationException($violations);
                }
                return $products;
            }
        }

        throw new ParserNotSupportedException("No parser supports the given URL: $url");
    }
}
