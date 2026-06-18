<?php

/**
 * NoteListInputFiltersDto resolver test.
 */

namespace App\Tests\Resolver;

use App\Dto\NoteListInputFiltersDto;
use App\Resolver\NoteListInputFiltersDtoResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Note filters resolver tests.
 */
class NoteListInputFiltersDtoResolverTest extends TestCase
{
    private NoteListInputFiltersDtoResolver $resolver;

    /**
     * Creates resolver instance for tests.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new NoteListInputFiltersDtoResolver();
    }

    /**
     * Resolver should not handle arguments with an unsupported type.
     */
    public function testResolveReturnsEmptyForUnsupportedType(): void
    {
        $request = Request::create('/note');
        $argument = new ArgumentMetadata('filters', Request::class, false, false, null);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        self::assertSame([], $result);
    }

    /**
     * Resolver should not handle arguments without a declared type.
     */
    public function testResolveReturnsEmptyWhenTypeIsNull(): void
    {
        $request = Request::create('/note');
        $argument = new ArgumentMetadata('filters', null, false, false, null);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        self::assertSame([], $result);
    }

    /**
     * Resolver should build a DTO with null filters when query params are missing.
     */
    public function testResolveReturnsDtoWithNullFiltersWhenNoQueryParams(): void
    {
        $request = Request::create('/note');
        $argument = $this->createFiltersArgument();

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        self::assertCount(1, $result);
        self::assertInstanceOf(NoteListInputFiltersDto::class, $result[0]);
        self::assertNull($result[0]->categoryId);
        self::assertNull($result[0]->tagId);
    }

    /**
     * Resolver should read categoryId and tagId from the query string.
     */
    public function testResolveReturnsDtoWithCategoryIdAndTagIdFromQuery(): void
    {
        $request = Request::create('/note', Request::METHOD_GET, [
            'categoryId' => '12',
            'tagId' => '34',
        ]);
        $argument = $this->createFiltersArgument();

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        self::assertCount(1, $result);
        self::assertInstanceOf(NoteListInputFiltersDto::class, $result[0]);
        self::assertSame(12, $result[0]->categoryId);
        self::assertSame(34, $result[0]->tagId);
    }

    /**
     * Resolver should pass only categoryId when tagId is omitted.
     */
    public function testResolveReturnsDtoWithOnlyCategoryId(): void
    {
        $request = Request::create('/note', Request::METHOD_GET, ['categoryId' => '7']);
        $argument = $this->createFiltersArgument();

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        self::assertCount(1, $result);
        self::assertSame(7, $result[0]->categoryId);
        self::assertNull($result[0]->tagId);
    }

    /**
     * Builds argument metadata for filters DTO.
     *
     * @return ArgumentMetadata Filters DTO argument metadata
     */
    private function createFiltersArgument(): ArgumentMetadata
    {
        return new ArgumentMetadata(
            'filters',
            NoteListInputFiltersDto::class,
            false,
            false,
            null
        );
    }
}
