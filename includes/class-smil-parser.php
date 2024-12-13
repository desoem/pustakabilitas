<?php
class Pustakabilitas_Smil_Parser {
    private $smil_file;
    private $segments = [];
    private $current_text = '';
    
    public function __construct($smil_path) {
        $this->smil_file = $smil_path;
        $this->parse_smil();
    }

    private function parse_smil() {
        $dom = new DOMDocument();
        $dom->load($this->smil_file);
        
        // Parse <seq> elements for main sequence
        $sequences = $dom->getElementsByTagName('seq');
        foreach ($sequences as $seq) {
            $this->parse_sequence($seq);
        }
    }

    private function parse_sequence($seq) {
        $pars = $seq->getElementsByTagName('par');
        foreach ($pars as $par) {
            $audio = $par->getElementsByTagName('audio')->item(0);
            $text = $par->getElementsByTagName('text')->item(0);
            
            if ($audio && $text) {
                $this->segments[] = [
                    'begin' => $this->parse_clip_begin($audio),
                    'end' => $this->parse_clip_end($audio),
                    'src' => $text->getAttribute('src'),
                    'text_id' => $text->getAttribute('id')
                ];
            }
        }
    }

    private function parse_clip_begin($audio) {
        return $this->parse_time($audio->getAttribute('clip-begin'));
    }

    private function parse_clip_end($audio) {
        return $this->parse_time($audio->getAttribute('clip-end'));
    }

    private function parse_time($time_str) {
        // Convert SMIL time format (npt=ss.ms) to seconds
        if (preg_match('/npt=(\d+\.?\d*)s/', $time_str, $matches)) {
            return floatval($matches[1]);
        }
        return 0;
    }

    public function get_segments() {
        return $this->segments;
    }

    public function get_text_at_time($current_time) {
        foreach ($this->segments as $segment) {
            if ($current_time >= $segment['begin'] && $current_time <= $segment['end']) {
                return $segment;
            }
        }
        return null;
    }
} 