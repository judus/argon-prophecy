<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Server;

use ArrayObject;
use Exception;
use Maduser\Argon\Http\Message\Response;
use Maduser\Argon\Http\Server\ResultContext;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ResultContextTest extends TestCase
{
    public function testSetAndGet(): void
    {
        $context = new ResultContext();
        $context->set('test');

        $this->assertSame('test', $context->get());
    }

    public function testHas(): void
    {
        $context = new ResultContext();
        $this->assertFalse($context->has());

        $context->set('something');
        $this->assertTrue($context->has());
    }

    public function testIsString(): void
    {
        $context = new ResultContext();
        $context->set('hello');

        $this->assertTrue($context->isString());
    }

    public function testIsScalar(): void
    {
        $context = new ResultContext();

        $context->set(42);
        $this->assertTrue($context->isScalar());

        $context->set(true);
        $this->assertTrue($context->isScalar());

        $context->set(3.14);
        $this->assertTrue($context->isScalar());

        $context->set('text');
        $this->assertTrue($context->isScalar());

        $context->set(['array']);
        $this->assertFalse($context->isScalar());
    }

    public function testIsClosure(): void
    {
        $context = new ResultContext();
        $context->set(fn () => 'ok');

        $this->assertTrue($context->isClosure());
    }

    public function testIsResponse(): void
    {
        $context = new ResultContext();
        $context->set(Response::text(''));

        $this->assertTrue($context->isResponse());
    }

    public function testIsArray(): void
    {
        $context = new ResultContext();
        $context->set(['foo' => 'bar']);

        $this->assertTrue($context->isArray());
    }

    public function testIsObject(): void
    {
        $context = new ResultContext();
        $context->set(new stdClass());

        $this->assertTrue($context->isObject());
    }

    public function testIsCallable(): void
    {
        $context = new ResultContext();

        $context->set(fn () => 'ok');
        $this->assertTrue($context->isCallable());

        $context->set(new class {
            public function __invoke()
            {
            }
        });
        $this->assertTrue($context->isCallable());
    }

    public function testIsSpecificClass(): void
    {
        $context = new ResultContext();
        $context->set(new ArrayObject());

        $this->assertTrue($context->is(ArrayObject::class));
        $this->assertFalse($context->is(Exception::class));
    }
}
