<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
class Pustakabilitas_Daisy_Parser {
    private $ncc_file;
    private $book_data;
    private $current_level = 0;
    private $heading_levels = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

    public function __construct($ncc_path) {
        $this->ncc_file = $ncc_path;
        $this->book_data = [
            'title' => '',
            'author' => '',
            'narrator' => '',
            'total_time' => '',
            'sections' => [],
            'pages' => [],
            'notes' => [],
            'metadata' => [],
            'toc' => [],
            'production_notes' => []
        ];
        $this->parse_ncc();
    }

    private function parse_ncc() {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTMLFile($this->ncc_file);
        libxml_clear_errors();

        // Parse metadata
        $this->parse_metadata($dom);
        
        // Parse table of contents
        $this->parse_toc($dom);
        
        // Parse pages
        $this->parse_pages($dom);
        
        // Parse production notes
        $this->parse_production_notes($dom);
    }

    private function parse_metadata($dom) {
        $metas = $dom->getElementsByTagName('meta');
        foreach ($metas as $meta) {
            $name = $meta->getAttribute('name');
            $content = $meta->getAttribute('content');
            
            switch (strtolower($name)) {
                case 'dc:title':
                    $this->book_data['title'] = $content;
                    break;
                case 'dc:creator':
                    $this->book_data['author'] = $content;
                    break;
                case 'ncc:narrator':
                    $this->book_data['narrator'] = $content;
                    break;
                case 'ncc:totaltime':
                    $this->book_data['total_time'] = $this->parse_time_string($content);
                    break;
                case 'ncc:depth':
                    $this->book_data['depth'] = intval($content);
                    break;
                default:
                    $this->book_data['metadata'][$name] = $content;
            }
        }
    }

    private function parse_toc($dom) {
        foreach ($this->heading_levels as $level => $tag) {
            $headings = $dom->getElementsByTagName($tag);
            foreach ($headings as $heading) {
                $anchor = $heading->getElementsByTagName('a')->item(0);
                if ($anchor) {
                    $section = [
                        'level' => $level + 1,
                        'title' => trim($anchor->textContent),
                        'href' => $anchor->getAttribute('href'),
                        'time' => $this->parse_time_attr($anchor),
                        'class' => $heading->getAttribute('class'),
                        'id' => $anchor->getAttribute('id'),
                        'children' => []
                    ];

                    // Handle SMIL file reference
                    $smil_ref = $this->extract_smil_reference($anchor->getAttribute('href'));
                    if ($smil_ref) {
                        $section['smil_ref'] = $smil_ref;
                    }

                    $this->add_to_toc($section);
                }
            }
        }
    }

    private function add_to_toc($section) {
        if ($section['level'] === 1) {
            $this->book_data['toc'][] = $section;
        } else {
            $this->add_to_parent($this->book_data['toc'], $section);
        }
    }

    private function add_to_parent(&$parents, $section) {
        for ($i = count($parents) - 1; $i >= 0; $i--) {
            if ($parents[$i]['level'] < $section['level']) {
                $parents[$i]['children'][] = $section;
                return;
            }
        }
    }

    private function parse_pages($dom) {
        $spans = $dom->getElementsByTagName('span');
        foreach ($spans as $span) {
            $class = $span->getAttribute('class');
            if (in_array($class, ['page-normal', 'page-front', 'page-special'])) {
                $anchor = $span->getElementsByTagName('a')->item(0);
                if ($anchor) {
                    $this->book_data['pages'][] = [
                        'type' => $class,
                        'number' => trim($anchor->textContent),
                        'href' => $anchor->getAttribute('href'),
                        'time' => $this->parse_time_attr($anchor),
                        'id' => $anchor->getAttribute('id')
                    ];
                }
            }
        }
    }

    private function parse_production_notes($dom) {
        $notes = $dom->getElementsByTagName('div');
        foreach ($notes as $note) {
            if ($note->getAttribute('class') === 'prod-note') {
                $this->book_data['production_notes'][] = [
                    'content' => trim($note->textContent),
                    'id' => $note->getAttribute('id'),
                    'time' => $this->parse_time_attr($note)
                ];
            }
        }
    }

    private function parse_time_attr($element) {
        $time = $element->getAttribute('data-time');
        if (!$time) {
            // Try to parse from SMIL reference
            $href = $element->getAttribute('href');
            if ($href && preg_match('/\#time=(\d+(\.\d+)?)/', $href, $matches)) {
                return floatval($matches[1]);
            }
        }
        return $time ? floatval($time) : 0;
    }

    private function parse_time_string($time_str) {
        // Parse time strings like "1:23:45.678"
        $parts = explode(':', $time_str);
        $seconds = 0;
        
        if (count($parts) === 3) {
            $seconds = ($parts[0] * 3600) + ($parts[1] * 60) + floatval($parts[2]);
        }
        
        return $seconds;
    }

    private function extract_smil_reference($href) {
        if (preg_match('/(.+\.smil)#(.+)/', $href, $matches)) {
            return [
                'file' => $matches[1],
                'anchor' => $matches[2]
            ];
        }
        return null;
    }

    public function get_book_data() {
        return $this->book_data;
    }

    public function get_section_by_time($time) {
        foreach ($this->book_data['toc'] as $section) {
            if ($time >= $section['time'] && $time < ($section['time'] + 1)) {
                return $section;
            }
        }
        return null;
    }
} 