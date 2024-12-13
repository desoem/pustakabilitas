<?php
class Pustakabilitas_Bookmark_Handler {
    private $book_id;
    private $user_id;
    
    public function __construct($book_id, $user_id) {
        $this->book_id = $book_id;
        $this->user_id = $user_id;
    }

    public function save_bookmark($data) {
        $bookmarks = get_user_meta($this->user_id, 'daisy_bookmarks', true) ?: [];
        
        $bookmark = [
            'id' => uniqid(),
            'book_id' => $this->book_id,
            'position' => $data['position'],
            'title' => $data['title'],
            'notes' => $data['notes'],
            'timestamp' => current_time('mysql'),
            'section' => $data['section'],
            'level' => $data['level']
        ];
        
        $bookmarks[] = $bookmark;
        update_user_meta($this->user_id, 'daisy_bookmarks', $bookmarks);
        
        return $bookmark;
    }

    public function get_bookmarks() {
        $all_bookmarks = get_user_meta($this->user_id, 'daisy_bookmarks', true) ?: [];
        return array_filter($all_bookmarks, function($bookmark) {
            return $bookmark['book_id'] == $this->book_id;
        });
    }

    public function delete_bookmark($bookmark_id) {
        $bookmarks = get_user_meta($this->user_id, 'daisy_bookmarks', true) ?: [];
        
        $bookmarks = array_filter($bookmarks, function($bookmark) use ($bookmark_id) {
            return $bookmark['id'] != $bookmark_id;
        });
        
        update_user_meta($this->user_id, 'daisy_bookmarks', array_values($bookmarks));
        return true;
    }
} 