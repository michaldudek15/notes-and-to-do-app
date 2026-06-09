<?php

/**
 * TaskListInputFiltersDto resolver test.
 */

namespace App\Tests\Resolver;

use App\Dto\TaskListInputFiltersDto;
use App\Resolver\TaskListInputFiltersDtoResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class TaskListInputFiltersDtoResolverTest extends TestCase
{
    private TaskListInputFiltersDtoResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new TaskListInputFiltersDtoResolver();
    }

    public function testResolveReturnsEmptyForUnsupportedType(): void
    {
        $request = Request::create('/task');
        $argument = new ArgumentMetadata('filters', Request::class, false, false, null);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        self::assertSame([], $result);
    }

    public function testResolveReturnsEmptyWhenTypeIsNull(): void
    {
        $request = Request::create('/task');
        $argument = new ArgumentMetadata('filters', null, false, false, null);

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        self::assertSame([], $result);
    }

    public function testResolveReturnsDtoWithNullFiltersWhenNoQueryParams(): void
    {
        $request = Request::create('/task');
        $argument = $this->createFiltersArgument();

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        self::assertCount(1, $result);
        self::assertInstanceOf(TaskListInputFiltersDto::class, $result[0]);
        self::assertNull($result[0]->categoryId);
        self::assertNull($result[0]->tagId);
    }

    public function testResolveReturnsDtoWithCategoryIdAndTagIdFromQuery(): void
    {
        $request = Request::create('/task', Request::METHOD_GET, [
            'categoryId' => '12',
            'tagId' => '34',
        ]);
        $argument = $this->createFiltersArgument();

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        self::assertCount(1, $result);
        self::assertInstanceOf(TaskListInputFiltersDto::class, $result[0]);
        self::assertSame(12, $result[0]->categoryId);
        self::assertSame(34, $result[0]->tagId);
    }

    public function testResolveReturnsDtoWithOnlyCategoryId(): void
    {
        $request = Request::create('/task', Request::METHOD_GET, ['categoryId' => '7']);
        $argument = $this->createFiltersArgument();

        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        self::assertCount(1, $result);
        self::assertSame(7, $result[0]->categoryId);
        self::assertNull($result[0]->tagId);
    }

    private function createFiltersArgument(): ArgumentMetadata
    {
        return new ArgumentMetadata(
            'filters',
            TaskListInputFiltersDto::class,
            false,
            false,
            null
        );
    }
}
