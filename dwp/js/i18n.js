export class I18N {
    static EN = 'EN';
    static ID = 'ID';
    static langage = I18N.ID;
    static NO_TRANSLATION = `NO TRANSLATION AVAILABLE (${this.langage})!`;
    static parameter_1 = '$1$';
    static parameter_2 = '$2$';

    static translations = Object.freeze({
        'DWP_ERROR': {
            ID: 'Daisy Web Player Error',
            EN: 'Daisy Web Player Error'
        },
        'NO_NCC': {
            ID: 'ncc=... parameter tidak spesifik !',
            EN: 'The ncc=... parameter has not been specified !'
        },
        'INVALID_NCC': {
            ID: `parameter ncc "${this.parameter_1}" tidak valid !`,
            EN: `The ncc parameter "${this.parameter_1}" is not valid !`
        },
        'TEXT_DISPLAY': {
            ID: 'Tampilan teks lengkap',
            EN: 'Full text display'
        },
        'ADD_BOOKMARK': {
            ID: 'Tambah bookmark',
            EN: 'Add a bookmark'
        },
        'GO_BACK_5_SECS': {
            ID: 'Mundur 5 detik',
            EN: 'Go 5 seconds back'
        },
        'GO_FWRD_5_SECS': {
            ID: 'Maju 5 detik',
            EN: 'Go 5 seconds forward'
        },
        'SPEED_1_0': {
            ID: 'Putar dengan kecepatan normal',
            EN: 'Play at normal speed'
        },
        'SPEED_1_5': {
            ID: 'Putar 1,5 kali lebih cepat',
            EN: 'Play at 1.5 times faster'
        },
        'SPEED_2_0': {
            ID: 'Putar 2 kali lebih cepat',
            EN: 'Play at 2 times faster'
        },
        'VOLUME_INC': {
            ID: 'Tingkatkan volume pemutaran',
            EN: 'Increase playback volume'
        },
        'VOLUME_DEC': {
            ID: 'Kurangi volume pemutaran',
            EN: 'Decrease playback volume'
        },
        'NAV_LEVEL_INC': {
            ID: 'Tingkatkan level navigasi',
            EN: 'Increase navigation level'
        },
        'NAV_LEVEL_DEC': {
            ID: 'Turunkan level navigasi',
            EN: 'Decrease navigation level'
        },
        'NAV_SECTION_NEXT': {
            ID: 'Lompat ke bagian berikutnya',
            EN: 'Jump to next section'
        },
        'NAV_SECTION_PREV': {
            ID: 'Lompat ke bagian sebelumnya',
            EN: 'Jump to previous section'
        },
        'CLIP_CURRENT_POS': {
            ID: 'Posisi saat ini',
            EN: 'Current position'
        },
        'CLIP_DURATION': {
            ID: 'Durasi klip audio',
            EN: 'Audio clip duration'
        },
        'PLAYBACK_VOLUME': {
            ID: 'Volume audio',
            EN: 'Audio volume'
        },
        'AUDIO_EVENT_LATEST': {
            ID: 'Audio terakhir',
            EN: 'Last audio event'
        },
        'PLAYBACK_RATE': {
            ID: 'Kecepatan pemutaran',
            EN: 'Playback rate'
        },
        'NAV_LEVEL_CURRENT': {
            ID: 'Level navigasi saat ini',
            EN: 'Current navigation level'
        },
        'NAV_LEVEL': {
            ID: 'Level navigasi',
            EN: 'Navigation level'
        },
        'BOOKMARKS': {
            ID: 'Bookmark',
            EN: 'Bookmarks'
        },
        'METADATA': {
            ID: 'Metadata',
            EN: 'Metadata'
        },
        'METADATA_LONG': {
            ID: 'Metadata buku',
            EN: 'Book metadata'
        },
        'PROPERTY': {
            ID: 'Properti',
            EN: 'Property'
        },
        'VALUE': {
            ID: 'Nilai',
            EN: 'Value'
        },
        'SCHEME': {
            ID: 'Skema',
            EN: 'Scheme'
        },
        'SETTINGS': {
            ID: 'Pengaturan',
            EN: 'Settings'
        },
        'READER': {
            ID: 'Pembaca',
            EN: 'Reader'
        },
        'DISPLAY_READER': {
            ID: 'Pembaca tampilan',
            EN: 'Display reader'
        },
        'STATUS_POSITION': {
            ID: 'Posisi',
            EN: 'Position'
        },
        'STATUS_DURATION': {
            ID: 'Durasi',
            EN: 'Duration'
        },
        'STATUS_VOLUME': {
            ID: 'Volume (0..100)',
            EN: 'Volume (0..100)'
        },
        'STATUS_EVENT': {
            ID: 'Audio event',
            EN: 'Audio event'
        },
        'STATUS_RATE': {
            ID: 'Kecepatan',
            EN: 'Rate'
        },
        'STATUS_REQ_LEVEL': {
            ID: 'Level',
            EN: 'Level'
        },
        'STATUS_CUR_LEVEL': {
            ID: 'Level saat ini',
            EN: 'Current level'
        },
        'BOOK_NAV': {
            ID: 'Navigasi buku',
            EN: 'Book navigation'
        },
        'AUDIO_NAV': {
            ID: 'Navigasi klip audio',
            EN: 'Audio clip navigation'
        },
        'VARIOUS_NAV': {
            ID: 'Item navigasi lainnya (volume, tingkat pemutaran)',
            EN: 'Other navigation items (volume, playback rate)'
        },
        'SOFTWARE_INFOS': {
            ID: 'Informasi perangkat lunak',
            EN: 'Software informations'
        },
        '_': {
            ID: '',
            EN: ''
        },
        'TOC': {
            ID: 'Daftar isi',
            EN: 'Table of content'
        },
        'PLAY': {
            ID: 'Putar',
            EN: 'Play'
        },
        'PAUSE': {
            ID: 'Jeda',
            EN: 'Pause'
        },
        'BOOKMARK_DELETE': {
            ID: 'Hapus bookmark',
            EN: 'Delete this bookmark'
        },
        'ERROR_NCC_TITLE': {
            ID: 'Daisy player tidak dapat bekerja dengan NCC yang tidak valid (judul tidak ada) !',
            EN: 'The Daisy player cannot work with an invalid NCC (the title is missing) !'
        }
    });

    /**
     * Set the translator langage.
     * 
     * @param {*} lang Langage ISO code (`EN`, `ID`, ...)
     */

    static set_lang(lang) {
        if (typeof lang === 'string') {
            switch (lang.toLowerCase()) {
                case 'en': I18N.langage = I18N.EN; break;
                case 'id': I18N.langage = I18N.ID; break;
            }
        }
    }

    /**
     * Translate the message identified by its keyword.
     * 
     * @param {*} kw The keyword
     * @returns The translated string or 'NO TRANSLATION'
     */

    static Tr(kw) {
        return I18N.translations[kw][I18N.langage] || I18N.NO_TRANSLATION;
    }
}