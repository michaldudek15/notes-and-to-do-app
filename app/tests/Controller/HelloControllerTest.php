<?php

/**
 * Hello World controller tests.
 */

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

/**
 * class HelloControllerTest.
 */
class HelloControllerTest extends WebTestCase
{
    /**
     * Test '/hello' route.
     */
    public function testHelloWorldRoute(): void
    {
        // given
        $client = static::createClient();

        // when
        $client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, '/hello');
        $resultHttpStatusCode = $client->getResponse()->getStatusCode();

        // then
        $this->assertEquals(200, $resultHttpStatusCode);
    }

    /**
     * Home route redirects guests to login page.
     */
    public function testRootRedirectsToLogin(): void
    {
        $client = static::createClient();
        $client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, '/');

        $this->assertResponseRedirects('/login');
        $this->assertEquals(\Symfony\Component\HttpFoundation\Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
    }

    /**
     * Invalid path parameter should return 404.
     */
    public function testHelloWithInvalidNameReturns404(): void
    {
        $client = static::createClient();
        $client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, '/hello/123');

        $this->assertResponseStatusCodeSame(404);
    }

    /**
     * POST method is not allowed on hello route.
     */
    public function testHelloPostMethodNotAllowed(): void
    {
        $client = static::createClient();
        $client->request(\Symfony\Component\HttpFoundation\Request::METHOD_POST, '/hello/Ann');

        $this->assertResponseStatusCodeSame(405);
    }

    /**
     * Named route should generate an accessible URL.
     */
    public function testHelloRouteIsAccessibleByName(): void
    {
        $client = static::createClient();
        $router = $client->getContainer()->get(RouterInterface::class);
        $url = $router->generate('hello_index', ['name' => 'Ann']);
        $client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, $url);

        $this->assertResponseIsSuccessful();
    }

    /**
     * Test default greetings.
     */
    public function testDefaultGreetings(): void
    {
        // given
        $client = static::createClient();

        // when
        $client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, '/hello');

        // then
        $this->assertSelectorTextContains('html title', 'Hello World!');
        $this->assertSelectorTextContains('html p', 'Hello World!');
    }

    /**
     * Test pesonalized greetings.
     *
     * @param string $name              Name
     * @param string $expectedGreetings Expected greetings
     *
     * @dataProvider dataProviderForTestPersonalizedGreetings
     */
    public function testPersonalizedGreetings(string $name, string $expectedGreetings): void
    {
        // given
        $client = static::createClient();

        // when
        $client->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, '/hello/'.$name);

        // then
        $this->assertSelectorTextContains('html title', $expectedGreetings);
        $this->assertSelectorTextContains('html p', $expectedGreetings);
    }

    /**
     * Data provider for testPersonalizedGreetings() method.
     *
     * @return \Generator Test case
     */
    public function dataProviderForTestPersonalizedGreetings(): \Generator
    {
        yield 'Hello Ann' => [
            'name' => 'Ann',
            'expectedGreetings' => 'Hello Ann!',
        ];
        yield 'Hello John' => [
            'name' => 'John',
            'expectedGreetings' => 'Hello John!',
        ];
        yield 'Hello Beth' => [
            'name' => 'Beth',
            'expectedGreetings' => 'Hello Beth!',
        ];
    }
}
