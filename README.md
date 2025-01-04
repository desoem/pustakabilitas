# Pustakabilitas Plugin (Beta Version)

## English

### Description
Pustakabilitas is a WordPress plugin designed to provide accessible digital library functionality, especially for visually impaired users. It allows users to browse, manage, and access books in an inclusive and user-friendly manner.

### Features
- Accessible interface following WCAG 2.2 guidelines
- Custom post type for digital books (PDF, EPUB, Audio)
- User dashboard for managing book collections
- User registration with admin approval system
- Bookmark system for audio books
- Activity tracking for downloads and reading
- Import/export books via CSV with progress tracking
- Multi-language support
- Compatible with Elementor and Hello Elementor theme
- AJAX-powered pagination for book grids
- Elementor widgets integration:
  - Books Grid Widget with advanced filtering
  - Statistics Widget
  - Book Search Widget

### Requirements
- WordPress 6.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

### Installation
1. Download the plugin
2. Upload the `pustakabilitas` folder to `/wp-content/plugins/`
3. Activate the plugin through 'Plugins' menu in WordPress
4. Configure settings from the Pustakabilitas menu

### Usage

#### Shortcodes
```
[pustakabilitas_user_dashboard]
- Displays user dashboard with navigation
- Shows recent activity
- Access to books, history, and bookmarks

[pustakabilitas_books]
- Displays book grid/list
- Parameters: category, tag, limit, orderby, order
```

#### Developer Hooks

Actions:
```php
// Called when book is downloaded
do_action('pustakabilitas_book_downloaded', $book_id, $user_id);

// Called when book is read
do_action('pustakabilitas_book_read', $book_id, $user_id);

// Called when bookmark is saved
do_action('pustakabilitas_bookmark_saved', $book_id, $user_id, $position);
```

Filters:
```php
// Modify book query
apply_filters('pustakabilitas_books_query_args', $args);

// Modify book output format
apply_filters('pustakabilitas_book_output', $output, $book);
```

### Compatibility
Tested with themes:
- Hello Elementor
- Astra

### Support
For help and questions, please create an issue on GitHub or contact support@pustakabilitas.com

---

## Bahasa Indonesia

### Deskripsi
Pustakabilitas adalah plugin WordPress yang dirancang untuk menyediakan fungsi perpustakaan digital yang aksesibel, terutama untuk pengguna tunanetra. Plugin ini memungkinkan pengguna untuk menjelajah, mengelola, dan mengakses buku secara inklusif dan ramah pengguna.

### Fitur
#### Manajemen Buku
- Custom post type untuk buku digital
- Metadata untuk file buku (PDF, EPUB, Audio)
- Kategori dan tag untuk pengorganisasian
- Import/export data buku via CSV dengan progress bar
- Batch processing untuk import file besar
- Pagination AJAX untuk grid buku
- Widget Elementor untuk menampilkan buku

#### Integrasi Elementor
- Widget Grid Buku dengan filter dan pagination
- Widget Statistik Perpustakaan
- Widget Pencarian Buku
- Styling options yang dapat dikustomisasi
- Responsive layout untuk semua device

#### Manajemen User
- Sistem registrasi user dengan approval
- Role khusus: Pending Subscriber dan Approved Subscriber
- Halaman admin untuk approval user
- Email notifikasi approval/rejection
- Pembatasan akses berdasarkan role

#### Dashboard User
- Dashboard personal untuk setiap user
- Tracking aktivitas membaca dan download
- Riwayat buku yang dibaca/didownload
- Sistem bookmark untuk audio book
- Progress tracking untuk setiap buku

### Persyaratan
- WordPress 6.0 atau lebih baru
- PHP 7.4 atau lebih baru
- MySQL 5.6 atau lebih baru

### Instalasi
1. Unduh plugin
2. Unggah folder `pustakabilitas` ke direktori `/wp-content/plugins/`
3. Aktifkan plugin melalui menu 'Plugins' di WordPress
4. Konfigurasikan pengaturan melalui menu Pustakabilitas

### Penggunaan

#### Shortcodes
```
[pustakabilitas_user_dashboard]
- Menampilkan dashboard user dengan menu navigasi
- Menampilkan aktivitas terbaru user
- Akses ke daftar buku, riwayat, dan bookmark

[pustakabilitas_books]
- Menampilkan grid/list buku
- Parameter: category, tag, limit, orderby, order
```

#### Import Buku
1. Buka menu 'Books > Import/Export'
2. Upload file CSV dengan format yang sesuai
3. Kolom yang diperlukan:
   - title: Judul buku
   - content: Deskripsi buku
   - book_file: URL file PDF
   - audio_url: URL file audio
   - epub_url: URL file EPUB
   - featured_image_url: URL gambar sampul
   - status: publish/draft/private
   - categories: Kategori (comma-separated)
   - tags: Tag (comma-separated)

#### Sistem User
1. User mendaftar melalui form registrasi
2. Status awal: Pending Subscriber
3. Admin approve/reject di menu 'Users > Pending Users'
4. User menerima email notifikasi hasil approval
5. User yang disetujui dapat:
   - Mengakses dan download buku
   - Menggunakan bookmark audio
   - Melihat riwayat aktivitas
   - Mengelola koleksi pribadi

#### Sistem Bookmark
1. Progress audio book tersimpan otomatis
2. User dapat menambah catatan pada bookmark
3. Bookmark tersimpan per user dan per buku
4. Lanjut membaca dari posisi terakhir

### Hooks untuk Developer

#### Actions
```php
// Dipanggil saat buku didownload
do_action('pustakabilitas_book_downloaded', $book_id, $user_id);

// Dipanggil saat buku dibaca
do_action('pustakabilitas_book_read', $book_id, $user_id);

// Dipanggil saat bookmark disimpan
do_action('pustakabilitas_bookmark_saved', $book_id, $user_id, $position);
```

#### Filters
```php
// Modifikasi query buku
apply_filters('pustakabilitas_books_query_args', $args);

// Modifikasi format output buku
apply_filters('pustakabilitas_book_output', $output, $book);
```

### Keamanan
- Validasi dan sanitasi semua input user
- Nonce verification untuk form submissions
- Capability checking untuk akses fitur
- Pembatasan upload file
- Proteksi halaman admin

### Kompatibilitas
Plugin ini telah diuji dengan tema:
- Hello Elementor
- Astra

### Dukungan
Untuk bantuan dan pertanyaan, silakan buat issue di repositori GitHub atau hubungi support@pustakabilitas.com

## Changelog

### 1.1.0
- Tambah integrasi widget Elementor:
  - Books Grid Widget dengan filter dan pagination
  - Statistics Widget
  - Book Search Widget
- Implementasi AJAX pagination untuk grid buku
- Tambah opsi tampilan grid yang responsif
- Tambah filter buku (terbaru, populer, kategori)
- Improve performa loading dengan AJAX
- Tambah custom styling options untuk widget
- Perbaikan bug dan peningkatan stabilitas

### 1.0.0
- Initial release
- Basic book management
- PDF dan audio book support
- Category dan tag system
- Import/export via CSV
