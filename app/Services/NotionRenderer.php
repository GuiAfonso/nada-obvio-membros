<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NotionRenderer
{
    private string $token;
    private string $version;
    private string $baseUrl = 'https://api.notion.com/v1';

    // tipos de propriedade que sabemos renderizar em tabela
    private array $tableTypes = [
        'title', 'rich_text', 'email', 'phone_number', 'url',
        'select', 'multi_select', 'number', 'checkbox', 'status',
        'date', 'rollup',
    ];

    // tipos a esconder (ruído em tabela)
    private array $skipTypes = ['relation', 'formula', 'people', 'files', 'created_by', 'last_edited_by', 'created_time', 'last_edited_time'];

    public function __construct()
    {
        $this->token   = config('notion.token');
        $this->version = config('notion.version');
    }

    private function http()
    {
        return Http::withHeaders([
            'Authorization'  => 'Bearer ' . $this->token,
            'Notion-Version' => $this->version,
        ]);
    }

    public function renderPage(string $pageId): string
    {
        $blocks = $this->fetchBlocks($pageId);
        return $this->renderBlocks($blocks);
    }

    private function fetchBlocks(string $blockId): array
    {
        $response = $this->http()->get("{$this->baseUrl}/blocks/{$blockId}/children");
        return $response->json('results') ?? [];
    }

    private function renderBlocks(array $blocks): string
    {
        $html = '';
        $i = 0;

        while ($i < count($blocks)) {
            $block = $blocks[$i];
            $type  = $block['type'];

            if ($type === 'bulleted_list_item') {
                $html .= '<ul class="notion-list">';
                while ($i < count($blocks) && $blocks[$i]['type'] === 'bulleted_list_item') {
                    $html .= '<li>' . $this->renderRichText($blocks[$i]['bulleted_list_item']['rich_text'] ?? []);
                    if ($blocks[$i]['has_children'] ?? false) {
                        $html .= $this->renderBlocks($this->fetchBlocks($blocks[$i]['id']));
                    }
                    $html .= '</li>';
                    $i++;
                }
                $html .= '</ul>';
                continue;
            }

            if ($type === 'numbered_list_item') {
                $html .= '<ol class="notion-list">';
                while ($i < count($blocks) && $blocks[$i]['type'] === 'numbered_list_item') {
                    $html .= '<li>' . $this->renderRichText($blocks[$i]['numbered_list_item']['rich_text'] ?? []);
                    if ($blocks[$i]['has_children'] ?? false) {
                        $html .= $this->renderBlocks($this->fetchBlocks($blocks[$i]['id']));
                    }
                    $html .= '</li>';
                    $i++;
                }
                $html .= '</ol>';
                continue;
            }

            $html .= $this->renderBlock($block);
            $i++;
        }

        return $html;
    }

    private function renderBlock(array $block): string
    {
        $type = $block['type'];
        $data = $block[$type] ?? [];
        $children = '';

        if ($block['has_children'] ?? false) {
            $children = $this->renderBlocks($this->fetchBlocks($block['id']));
        }

        return match ($type) {
            'paragraph'      => $this->wrap('p', 'notion-p', $this->renderRichText($data['rich_text'] ?? []) ?: '&nbsp;'),
            'heading_1'      => $this->wrap('h1', 'notion-h1', $this->renderRichText($data['rich_text'] ?? [])),
            'heading_2'      => $this->wrap('h2', 'notion-h2', $this->renderRichText($data['rich_text'] ?? [])),
            'heading_3'      => $this->wrap('h3', 'notion-h3', $this->renderRichText($data['rich_text'] ?? [])),
            'divider'        => '<hr class="notion-hr">',
            'quote'          => $this->wrap('blockquote', 'notion-quote', $this->renderRichText($data['rich_text'] ?? [])),
            'code'           => '<pre class="notion-code"><code>' . e($data['rich_text'][0]['plain_text'] ?? '') . '</code></pre>',
            'image'          => $this->renderImage($data),
            'callout'        => $this->renderCallout($data, $children),
            'toggle'         => $this->renderToggle($data, $children),
            'column_list'    => '<div class="notion-columns">' . $children . '</div>',
            'column'         => '<div class="notion-column">' . $children . '</div>',
            'child_page'     => $this->renderChildPage($block),
            'child_database' => $this->renderChildDatabase($block),
            'to_do'          => $this->renderTodo($data),
            'embed', 'bookmark' => $this->renderLink($data),
            default          => '',
        };
    }

    private function renderRichText(array $richText): string
    {
        $html = '';
        foreach ($richText as $rt) {
            $text  = e($rt['plain_text'] ?? '');
            $annot = $rt['annotations'] ?? [];
            $href  = $rt['href'] ?? null;

            if ($annot['bold'] ?? false)         $text = "<strong>{$text}</strong>";
            if ($annot['italic'] ?? false)        $text = "<em>{$text}</em>";
            if ($annot['strikethrough'] ?? false) $text = "<s>{$text}</s>";
            if ($annot['underline'] ?? false)     $text = "<u>{$text}</u>";
            if ($annot['code'] ?? false)          $text = "<code class='inline-code'>{$text}</code>";

            $color = $annot['color'] ?? 'default';
            if ($color !== 'default') {
                $text = "<span class='color-{$color}'>{$text}</span>";
            }

            if ($href) {
                $text = "<a href='" . e($href) . "' target='_blank' rel='noopener'>{$text}</a>";
            }

            $html .= $text;
        }
        return $html;
    }

    // ── child database ────────────────────────────────────────────────

    private function renderChildDatabase(array $block): string
    {
        $dbId  = str_replace('-', '', $block['id']);
        $title = $block['child_database']['title'] ?? 'Database';

        // busca schema
        $schema = $this->http()->get("{$this->baseUrl}/databases/{$dbId}");
        if (! $schema->successful()) {
            return ''; // linked database ou sem acesso — omite silenciosamente
        }

        $properties = $schema->json('properties') ?? [];

        // busca registros (máx 50)
        $rows = $this->http()->post(
            "{$this->baseUrl}/databases/{$dbId}/query",
            (object)['page_size' => 50]
        );

        $records = $rows->json('results') ?? [];

        if (empty($records)) {
            return '';
        }

        // title primeiro, depois demais tipos suportados, excluindo skipTypes
        $titleCol  = [];
        $otherCols = [];
        foreach ($properties as $name => $prop) {
            if (in_array($prop['type'], $this->skipTypes)) continue;
            if ($prop['type'] === 'title') {
                $titleCol[$name] = $prop['type'];
            } else {
                $otherCols[$name] = $prop['type'];
            }
        }

        // filtra colunas que têm pelo menos 1 valor nos primeiros registros
        $filledCols = [];
        foreach ($otherCols as $name => $type) {
            foreach (array_slice($records, 0, 20) as $record) {
                $val = $this->renderPropValue($record['properties'][$name] ?? [], $type);
                if (trim(strip_tags($val)) !== '') {
                    $filledCols[$name] = $type;
                    break;
                }
            }
        }

        // title + até 7 colunas com dados
        $columns = $titleCol + array_slice($filledCols, 0, 7, true);

        // monta tabela
        $html  = "<div class='notion-db-wrap'>";
        $html .= "<div class='notion-db-title'>🗃️ " . e($title) . "</div>";
        $html .= "<div class='notion-db-scroll'><table class='notion-db-table'><thead><tr>";

        foreach ($columns as $name => $type) {
            $html .= "<th>" . e($name) . "</th>";
        }
        $html .= "</tr></thead><tbody>";

        foreach ($records as $record) {
            $html .= "<tr>";
            foreach ($columns as $name => $type) {
                $val = $this->renderPropValue($record['properties'][$name] ?? [], $type);
                $html .= "<td>{$val}</td>";
            }
            $html .= "</tr>";
        }

        $html .= "</tbody></table></div></div>";
        return $html;
    }

    private function renderPropValue(array $prop, string $type): string
    {
        return match ($type) {
            'title'        => e($prop['title'][0]['plain_text'] ?? ''),
            'rich_text'    => e($prop['rich_text'][0]['plain_text'] ?? ''),
            'email'        => $prop['email'] ? "<a href='mailto:" . e($prop['email']) . "'>" . e($prop['email']) . "</a>" : '',
            'phone_number' => e($prop['phone_number'] ?? ''),
            'url'          => $prop['url'] ? "<a href='" . e($prop['url']) . "' target='_blank' rel='noopener'>" . e($prop['url']) . "</a>" : '',
            'number'       => $prop['number'] !== null ? (string) $prop['number'] : '',
            'checkbox'     => ($prop['checkbox'] ?? false) ? '✅' : '',
            'select'       => $prop['select'] ? $this->renderTag($prop['select']['name'], $prop['select']['color'] ?? 'default') : '',
            'status'       => $prop['status'] ? $this->renderTag($prop['status']['name'], $prop['status']['color'] ?? 'default') : '',
            'multi_select' => implode(' ', array_map(fn($o) => $this->renderTag($o['name'], $o['color'] ?? 'default'), $prop['multi_select'] ?? [])),
            'date'         => $prop['date']['start'] ?? '',
            'rollup'       => $this->renderRollup($prop['rollup'] ?? []),
            default        => '',
        };
    }

    private function renderRollup(array $rollup): string
    {
        $type = $rollup['type'] ?? '';
        return match ($type) {
            'number' => $rollup['number'] !== null ? (string) round($rollup['number'], 1) : '',
            'array'  => implode(', ', array_map(function ($item) {
                return e($item[$item['type']]['plain_text'] ?? $item[$item['type']]['name'] ?? '');
            }, array_filter($rollup['array'] ?? [], fn($i) => isset($i[$i['type']])))),
            default  => '',
        };
    }

    private function renderTag(string $name, string $color): string
    {
        return "<span class='notion-tag color-tag-{$color}'>" . e($name) . "</span>";
    }

    // ── outros blocos ────────────────────────────────────────────────

    private function renderImage(array $data): string
    {
        $url     = $data['file']['url'] ?? $data['external']['url'] ?? '';
        $caption = $this->renderRichText($data['caption'] ?? []);
        if (! $url) return '';
        $cap = $caption ? "<figcaption>{$caption}</figcaption>" : '';
        return "<figure class='notion-image'><img src='" . e($url) . "' alt=''>{$cap}</figure>";
    }

    private function renderCallout(array $data, string $children): string
    {
        $icon = $data['icon']['emoji'] ?? '💡';
        $text = $this->renderRichText($data['rich_text'] ?? []);
        return "<div class='notion-callout'><span class='callout-icon'>{$icon}</span><div><p>{$text}</p>{$children}</div></div>";
    }

    private function renderToggle(array $data, string $children): string
    {
        $text = $this->renderRichText($data['rich_text'] ?? []);
        return "<details class='notion-toggle' open><summary>{$text}</summary><div class='toggle-body'>{$children}</div></details>";
    }

    private function renderTodo(array $data): string
    {
        $checked = $data['checked'] ?? false;
        $text    = $this->renderRichText($data['rich_text'] ?? []);
        $check   = $checked ? '✅' : '⬜';
        $class   = $checked ? 'todo-checked' : '';
        return "<p class='notion-todo {$class}'>{$check} {$text}</p>";
    }

    private function renderChildPage(array $block): string
    {
        $title = $block['child_page']['title'] ?? 'Página';
        return "<div class='notion-child-page'>📄 " . e($title) . "</div>";
    }

    private function renderLink(array $data): string
    {
        $url = $data['url'] ?? '';
        if (! $url) return '';
        return "<a class='notion-bookmark' href='" . e($url) . "' target='_blank' rel='noopener'>" . e($url) . "</a>";
    }

    private function wrap(string $tag, string $class, string $content): string
    {
        return "<{$tag} class='{$class}'>{$content}</{$tag}>";
    }
}
