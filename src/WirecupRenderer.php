<?php

namespace Ruibeard\WirecupLaravel;

class WirecupRenderer
{
    public function render(string $contents, string $title = 'Wirecup'): string
    {
        $lines = preg_split('/\r\n|\r|\n/', $contents) ?: [];

        return $this->pageWrap($this->renderLines($lines), $title);
    }

    /**
     * @param list<string> $lines
     */
    protected function renderLines(array $lines): string
    {
        $parts = [];
        $listItems = [];
        $count = count($lines);

        $flushList = function () use (&$parts, &$listItems): void {
            if ($listItems === []) {
                return;
            }

            $parts[] = '<ul class="my-2 pl-5 list-disc">'.implode('', $listItems).'</ul>';
            $listItems = [];
        };

        for ($i = 0; $i < $count; $i++) {
            $raw = $lines[$i];
            $parsed = $this->parseLine($raw);

            if ($parsed === null) {
                continue;
            }

            [$type, $content] = $parsed;
            $level = $this->indentLevel($raw);

            if ($type === 'g' || $type === 'c' || $type === 'r') {
                $flushList();
                $childLines = [];

                while (($i + 1) < $count) {
                    $next = $lines[$i + 1];

                    if (trim($next) === '') {
                        $i++;

                        continue;
                    }

                    if ($this->indentLevel($next) <= $level) {
                        break;
                    }

                    $childLines[] = $next;
                    $i++;
                }

                if ($type === 'g') {
                    $parts[] = $this->renderTable($content, $childLines);
                }

                if ($type === 'c') {
                    $parts[] = '<div class="card my-3 p-4 border-2 border-stone-500 rounded-md bg-stone-50 sketchy-card">'.$this->renderLines($childLines).'</div>';
                }

                if ($type === 'r') {
                    $parts[] = '<div class="flex gap-4 items-start flex-wrap">'.$this->renderLines($childLines).'</div>';
                }

                continue;
            }

            if ($type === 'l') {
                $listItems[] = $this->elList($content);

                continue;
            }

            $flushList();

            $parts[] = match ($type) {
                'n' => $this->elNav($content),
                'h' => $this->elHeading($content),
                't' => $this->elText($content),
                'i' => $this->elInput($content),
                'b' => $this->elButton($content),
                'x' => $this->elImage($content),
                's' => $this->elSelect($content),
                '-' => $this->elDivider(),
                '=' => $this->elDivider(true),
                'v' => $this->elBadge($content),
                'a' => $this->elAlert($content),
                'k' => $this->elCheckbox($content),
                default => throw new \InvalidArgumentException("Unknown Wirecup element type '{$type}'"),
            };
        }

        $flushList();

        return implode("\n", $parts);
    }

    protected function pageWrap(string $inner, string $title): string
    {
        return '<!doctype html>'
            .'<html lang="en">'
            .'<head>'
            .'<meta charset="utf-8">'
            .'<meta name="viewport" content="width=device-width, initial-scale=1">'
            .'<title>'.$this->escape($title).'</title>'
            .$this->pageCss()
            .'</head>'
            .'<body class="min-h-screen p-8 md:p-12 font-kalam bg-stone-100">'
            .'<div class="page mx-auto bg-stone-50 p-8 border-2 border-stone-300 shadow-lg max-w-[900px] sketchy-page">'
            .$inner
            .'</div>'
            .'</body>'
            .'</html>';
    }

    protected function pageCss(): string
    {
        return <<<'HTML'
<script>
tailwind.config = {
  theme: {
    extend: {
      fontFamily: {
        kalam: ["Kalam", "Comic Sans MS", "cursive"],
      },
    },
  },
};
</script>
<script src="https://cdn.tailwindcss.com"></script>
<style>
.sketchy-page {
    transform: rotate(-0.3deg);
}

.sketchy-card {
    transform: rotate(0.2deg);
}

.sketchy-divider {
    transform: rotate(-0.1deg);
}
</style>
HTML;
    }

    /**
     * @return array{0: string, 1: string}|null
     */
    protected function parseLine(string $line): ?array
    {
        $stripped = ltrim($line);

        if ($stripped === '') {
            return null;
        }

        if ($stripped === '-') {
            return ['-', ''];
        }

        if ($stripped === '=') {
            return ['=', ''];
        }

        return [substr($stripped, 0, 1), ltrim(substr($stripped, 1))];
    }

    protected function indentLevel(string $line): int
    {
        return strlen($line) - strlen(ltrim($line));
    }

    protected function elNav(string $content): string
    {
        $items = [];

        foreach (preg_split('/\s+/', trim($content)) ?: [] as $item) {
            if ($item === '') {
                continue;
            }

            [$label, $href] = $this->parseLink($item);

            if ($href !== '') {
                $items[] = '<a href="'.$this->escapeAttribute($href).'" class="hover:underline text-stone-800">'.$this->escape($label).'</a>';

                continue;
            }

            $items[] = '<span class="hover:underline cursor-default text-stone-800">'.$this->escape($label).'</span>';
        }

        return '<nav class="flex gap-6 pb-2 mb-4 border-b-2 border-stone-500 text-[0.95em]">'.implode('', $items).'</nav>';
    }

    protected function elHeading(string $content): string
    {
        return '<h2 class="text-[1.6em] font-bold my-3 tracking-tight text-stone-800">'.$this->escape($content).'</h2>';
    }

    protected function elText(string $content): string
    {
        return '<p class="my-1.5 text-[0.95em] text-stone-600">'.$this->escape($content).'</p>';
    }

    protected function elInput(string $content): string
    {
        $placeholder = trim($content) !== '' ? trim($content) : '________';

        return '<div class="inline-block min-w-[200px] my-1.5 p-2.5 border-2 border-stone-500 rounded bg-neutral-50 text-stone-400 italic">'.$this->escape($placeholder).'</div>';
    }

    protected function elButton(string $content): string
    {
        [$label, $href] = $this->parseLink($content);
        $label = $label !== '' ? $label : 'OK';
        $classes = 'inline-block my-2 px-5 py-2 border-2 border-stone-700 rounded-md bg-stone-200 cursor-default shadow-sm active:shadow-none active:translate-x-px active:translate-y-0.5 text-stone-800 no-underline';

        if ($href !== '') {
            return '<a href="'.$this->escapeAttribute($href).'" class="'.$classes.'">'.$this->escape($label).'</a>';
        }

        return '<button class="'.$classes.'">'.$this->escape($label).'</button>';
    }

    protected function elImage(string $content): string
    {
        $label = trim($content) !== '' ? trim($content) : 'img';

        return '<div class="flex items-center justify-center my-1.5 min-h-20 min-w-[80px] bg-stone-200 border-2 border-stone-400 text-stone-400 text-[0.8em]" style="border-style:dashed">'.$this->escape($label).'</div>';
    }

    protected function elSelect(string $content): string
    {
        $label = trim($content) !== '' ? trim($content) : '...';

        return '<div class="relative inline-block my-1.5 pr-7 pl-2.5 py-2 border-2 border-stone-500 rounded bg-neutral-50">'.$this->escape($label).'<span class="absolute right-2 top-1/2 -translate-y-1/2">▾</span></div>';
    }

    protected function elList(string $content): string
    {
        return '<li class="my-1 pl-4 list-disc text-stone-800">'.$this->escape($content).'</li>';
    }

    protected function elDivider(bool $thick = false): string
    {
        $width = $thick ? '3px' : '2px';
        $color = $thick ? 'stone-500' : 'stone-400';

        return '<hr class="my-3 border-0 border-t-['.$width.'] border-'.$color.' sketchy-divider">';
    }

    protected function elBadge(string $content): string
    {
        return '<span class="inline-block my-0.5 mx-1 px-3 py-0.5 text-[0.8em] font-bold rounded-xl border-2 bg-stone-100 border-stone-400 text-stone-600">'.$this->escape(trim($content)).'</span>';
    }

    protected function elAlert(string $content): string
    {
        return '<div class="my-2.5 p-4 rounded-md border-2 text-[0.95em] bg-stone-100 border-stone-400 text-stone-700">'.$this->escape(trim($content)).'</div>';
    }

    protected function elCheckbox(string $content): string
    {
        return '<label class="flex items-center gap-2 my-1.5 text-[0.95em] text-stone-800"><span class="inline-block w-[18px] h-[18px] border-2 border-stone-600 rounded-[3px] shrink-0"></span><span>'.$this->escape(trim($content)).'</span></label>';
    }

    /**
     * @param list<string> $rows
     */
    protected function renderTable(string $headerLine, array $rows): string
    {
        $headers = $this->splitCells($headerLine);
        $headerHtml = implode('', array_map(
            fn (string $header): string => '<th class="px-3 py-2 border-2 border-stone-500 text-left bg-stone-200 font-bold">'.$this->escape($header).'</th>',
            $headers,
        ));
        $rowsHtml = [];

        foreach ($rows as $index => $row) {
            $cells = $this->splitCells($row);
            $bg = $index % 2 === 1 ? 'bg-stone-50' : '';
            $cellHtml = [];

            foreach ($cells as $cell) {
                $cell = trim($cell);

                if (str_starts_with($cell, 'v ')) {
                    $content = $this->elBadge(substr($cell, 2));
                } elseif (str_starts_with($cell, 'b ')) {
                    $content = $this->elButton(substr($cell, 2));
                } else {
                    $content = $this->escape($cell);
                }

                $cellHtml[] = '<td class="px-3 py-2 border-2 border-stone-500 '.$bg.'">'.$content.'</td>';
            }

            $rowsHtml[] = '<tr>'.implode('', $cellHtml).'</tr>';
        }

        return '<div class="my-2.5 overflow-x-auto"><table class="w-full border-collapse text-[0.9em]"><thead><tr>'.$headerHtml.'</tr></thead><tbody>'.implode('', $rowsHtml).'</tbody></table></div>';
    }

    /**
     * @return list<string>
     */
    protected function splitCells(string $line): array
    {
        $parts = preg_split('/\s{2,}/', trim($line)) ?: [];

        return array_values(array_filter(array_map(trim(...), $parts), static fn (string $part): bool => $part !== ''));
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function parseLink(string $content): array
    {
        [$label, $target] = $this->splitLink($content);

        if ($target === '') {
            return [$label, ''];
        }

        if (str_starts_with($target, 'http')) {
            $href = $target;
        } elseif (str_starts_with($target, '/')) {
            $href = $target;
        } elseif (str_ends_with($target, '.cup')) {
            $href = '/'.trim(substr($target, 0, -4), '/');
        } else {
            $href = '/'.trim($target, '/');
        }

        return [$label, $href];
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function splitLink(string $content): array
    {
        if (! str_contains($content, '|')) {
            return [trim($content), ''];
        }

        [$label, $target] = explode('|', $content, 2);

        return [trim($label), trim($target)];
    }
    protected function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    protected function escapeAttribute(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
