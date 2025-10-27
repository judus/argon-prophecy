<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use Maduser\Argon\Support\Html;
use PHPUnit\Framework\TestCase;

final class HtmlTest extends TestCase
{
    public function testCreateReturnsInstance(): void
    {
        $html = Html::create('<p>Hello</p>');

        $this->assertInstanceOf(Html::class, $html);
    }

    public function testToHtmlRendersTemplateWithContext(): void
    {
        $html = Html::create('<p>Hello, {{ name }}!</p>', [
            'name' => 'Prophet',
        ]);

        $this->assertSame('<p>Hello, Prophet!</p>', $html->toHtml());
    }

    public function testToHtmlReturnsSameRenderedResultOnSecondCall(): void
    {
        $html = Html::create('<p>{{ word }}</p>', [
            'word' => 'Test',
        ]);

        $firstRender = $html->toHtml();
        $secondRender = $html->toHtml();

        $this->assertSame($firstRender, $secondRender);
    }

    public function testToHtmlLeavesUnknownVariablesUnchanged(): void
    {
        $html = Html::create('<p>Hello, {{ name }} {{ unknown }}</p>', [
            'name' => 'Prophet',
        ]);

        $output = $html->toHtml();

        $this->assertStringContainsString('{{ unknown }}', $output);
        $this->assertStringContainsString('Prophet', $output);
    }

    public function testToStringCastsProperly(): void
    {
        $html = Html::create('<h1>{{ title }}</h1>', [
            'title' => 'Victory',
        ]);

        $this->assertSame('<h1>Victory</h1>', (string) $html);
    }
}
