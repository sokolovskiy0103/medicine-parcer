<?php

namespace App\Service;

use App\DTO\ProductDTO;
use App\Exception\ParserNotSupportedException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
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
                return $parser->parse($request->getContent());
            }
        }

        throw new ParserNotSupportedException("No parser supports the given URL: $url");
    }
}
