<?php
class Pustakabilitas_Production_Notes {
    private $book_id;
    
    public function __construct($book_id) {
        $this->book_id = $book_id;
    }

    public function get_production_notes() {
        return get_post_meta($this->book_id, 'daisy_production_notes', true) ?: [];
    }

    public function add_production_note($note) {
        $notes = $this->get_production_notes();
        $notes[] = [
            'id' => uniqid(),
            'content' => $note['content'],
            'time_code' => $note['time_code'],
            'type' => $note['type'],
            'timestamp' => current_time('mysql')
        ];
        
        update_post_meta($this->book_id, 'daisy_production_notes', $notes);
    }
} 