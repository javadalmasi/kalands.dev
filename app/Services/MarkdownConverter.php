<?php

namespace App\Services;

use League\CommonMark\CommonMarkConverter;

class MarkdownConverter
{
    public function convert(string $markdown): string
    {
        // First convert tables to HTML (before markdown processing)
        $markdown = $this->convertTablesToHtml($markdown);

        // Convert remaining markdown to HTML
        $converter = new CommonMarkConverter([
            'allow_unsafe_links' => false,
            'html_input' => 'allow',
        ]);

        return $converter->convert($markdown)->getContent();
    }

    private function convertTablesToHtml(string $markdown): string
    {
        // Split markdown by lines
        $lines = explode("\n", $markdown);
        $result = [];
        $i = 0;

        while ($i < count($lines)) {
            $line = $lines[$i];

            // Check if this line starts a table (has pipes and next line is separator)
            if (strpos($line, '|') !== false && isset($lines[$i + 1])) {
                $nextLine = $lines[$i + 1];
                if ($this->isSeparatorLine($nextLine)) {
                    // Found table header
                    $headerCells = $this->parseCells($line);

                    if (! empty($headerCells)) {
                        $html = '<table class="markdown-table"><thead><tr>';
                        foreach ($headerCells as $cell) {
                            $html .= '<th>'.htmlspecialchars($cell, ENT_QUOTES, 'UTF-8').'</th>';
                        }
                        $html .= '</tr></thead><tbody>';

                        // Skip separator line
                        $i += 2;

                        // Parse table rows
                        while ($i < count($lines)) {
                            $rowLine = trim($lines[$i]);

                            // Check if line is a table row
                            if (empty($rowLine) || strpos($rowLine, '|') === false) {
                                break;
                            }

                            $cells = $this->parseCells($rowLine);
                            if (! empty($cells)) {
                                $html .= '<tr>';
                                foreach ($cells as $cell) {
                                    $html .= '<td>'.htmlspecialchars($cell, ENT_QUOTES, 'UTF-8').'</td>';
                                }
                                $html .= '</tr>';
                            }

                            $i++;
                        }

                        $html .= '</tbody></table>';
                        $result[] = $html;

                        continue;
                    }
                }
            }

            $result[] = $line;
            $i++;
        }

        return implode("\n", $result);
    }

    private function isSeparatorLine(string $line): bool
    {
        $line = trim($line);

        // Check if line contains only pipes, dashes, colons, and spaces
        return preg_match('/^[\|\s\-:]+$/', $line) && strpos($line, '-') !== false;
    }

    private function parseCells(string $line): array
    {
        // Remove leading and trailing pipes
        $line = trim($line, '| ');

        // Split by pipe
        $cells = explode('|', $line);

        // Trim and filter
        $cells = array_map('trim', $cells);

        return array_filter($cells, fn ($cell) => $cell !== '');
    }
}
