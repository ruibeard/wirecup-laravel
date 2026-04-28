<?php

namespace Ruibeard\WirecupLaravel\Tests\Feature;

use Ruibeard\WirecupLaravel\Tests\TestCase;

class WirecupRendererTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $includeDir = config('wirecup.root').'/_includes';

        if (! is_dir($includeDir)) {
            mkdir($includeDir, 0777, true);
        }

        file_put_contents($includeDir.'/test-snippet.cup', "h$1\n"."t$*\n");
    }

    public function test_it_renders_common_elements(): void
    {
        $html = $this->renderer()->render(<<<'CUP'
 n Home|home Dashboard|/dashboard Docs|https://example.com
 h Title
 t Body text
 i Email address
 b Save|next
 x hero image
 s Choose one
 l first
 l second
 v approved
 a warning notice
 k Accept terms
 -
 =
CUP);

        $this->assertStringContainsString('Title', $html);
        $this->assertStringContainsString('Body text', $html);
        $this->assertStringContainsString('Email address', $html);
        $this->assertStringContainsString('href="/wirecup?file=next.cup"', $html);
        $this->assertStringContainsString('href="/wirecup/render/home.cup"', $html);
        $this->assertStringContainsString('href="/dashboard"', $html);
        $this->assertStringContainsString('hero image', $html);
        $this->assertStringContainsString('Choose one', $html);
        $this->assertStringContainsString('<ul class="my-2 pl-5 list-disc">', $html);
        $this->assertStringContainsString('bg-stone-100 border-stone-400 text-stone-600', $html);
        $this->assertStringContainsString('warning notice', $html);
        $this->assertStringContainsString('Accept terms', $html);
        $this->assertStringContainsString('tailwindcss.com', $html);
    }

    public function test_it_renders_cards_rows_and_tables(): void
    {
        $html = $this->renderer()->render(<<<'CUP'
 c
   h Card title
   t Card body
 r
   b Left
   b Right
 g Name  Status  Action
   Jane  v approved  b Edit|edit
   John  pending  b View|view
CUP);

        $this->assertStringContainsString('Card title', $html);
        $this->assertStringContainsString('flex gap-4 items-start', $html);
        $this->assertStringContainsString('<table class="w-full border-collapse text-[0.9em]">', $html);
        $this->assertStringContainsString('href="/wirecup?file=edit.cup"', $html);
        $this->assertStringContainsString('href="/wirecup?file=view.cup"', $html);
    }

    public function test_it_renders_includes_and_tab_separated_grids(): void
    {
        $html = $this->renderer()->render(<<<'CUP'
 u ballot-nav room-selection
 u test-snippet Compact mode works
 gName	Status	Action
  Jane	v approved	b Edit|edit
CUP);

        $this->assertStringContainsString('/wirecup/render/ballot-overview.cup', $html);
        $this->assertStringContainsString('Selection</span>', $html);
        $this->assertStringContainsString('Compact', $html);
        $this->assertStringContainsString('mode works', $html);
        $this->assertStringContainsString('href="/wirecup?file=edit.cup"', $html);
    }

    public function test_it_throws_for_unknown_elements(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->renderer()->render("z unknown");
    }

    public function test_it_throws_for_unknown_includes(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->renderer()->render("u missing-snippet");
    }
}
