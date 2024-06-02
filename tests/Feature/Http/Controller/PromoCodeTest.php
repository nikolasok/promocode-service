<?php

declare(strict_types=1);

namespace App\Tests\Feature\Http\Controller;

use App\Infrastructure\Http\Message\RedirectResponse;
use App\Infrastructure\Test\WebTestCase;

class PromoCodeTest extends WebTestCase
{
    public function testGetPage(): void
    {
        $response = $this->request('GET', '/');
        $content = $response->getContent();

        $doc = new \DOMDocument();
        $doc->loadHTML($content);
        $domNode = $doc->getElementsByTagName('form');
        ;
        self::assertSame(1, $domNode->length);
        $attributesItem = $domNode->item(0)?->attributes?->item(0);
        self::assertNotNull($attributesItem);
        self::assertSame('action', $attributesItem->nodeName);
        self::assertSame('/', $attributesItem->nodeValue);
        $attributesItem = $domNode->item(0)?->attributes?->item(1);
        self::assertNotNull($attributesItem);
        self::assertSame('method', $attributesItem->nodeName);
        self::assertSame('post', $attributesItem->nodeValue);

        $input = $domNode->item(0)?->childNodes->item(1);
        self::assertNotNull($input);
        self::assertSame('input', $input->nodeName);

        $attributesItem = $input->attributes?->item(0);
        self::assertNotNull($attributesItem);
        self::assertSame('type', $attributesItem->nodeName);
        self::assertSame('submit', $attributesItem->nodeValue);
    }

    public function testReservePromoCodeWithEmptyTable(): void
    {
        $response = $this->request('POST', '/');
        $content = $response->getContent();

        self::assertStringContainsString('There are no promo codes for you', $content);
    }

    public function testReservePromoCode(): void
    {
        $this->getDb()->getPdo()->exec('INSERT INTO promocode (code) values (\'12345678\')');
        $response = $this->request('POST', '/');
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('https://www.google.com/?query=12345678', $response->getHeaders()['location']);
        self::assertSame(302, $response->getStatus());
    }

    public function testReservePromoCodeRepeat(): void
    {
        $this->getDb()->getPdo()->exec('INSERT INTO promocode (code) values (\'12345678\')');
        $response = $this->request('POST', '/');
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('https://www.google.com/?query=12345678', $response->getHeaders()['location']);
        self::assertSame(302, $response->getStatus());

        $stmt = $this->getDb()->getPdo()->query('SELECT * FROM promocode');
        self::assertNotFalse($stmt);
        $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        self::assertCount(1, $res);
        self::assertNotNull($res[0]['device_id']);
        self::assertNotNull($res[0]['ip_long']);
        self::assertNotNull($res[0]['applied_at']);

        $response = $this->request('POST', '/');
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('https://www.google.com/?query=12345678', $response->getHeaders()['location']);
        self::assertSame(302, $response->getStatus());

        $_SESSION = [];
        $response = $this->request('POST', '/');
        $content = $response->getContent();

        self::assertStringContainsString('There are no promo codes for you', $content);
    }
}
