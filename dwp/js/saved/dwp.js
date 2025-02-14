// DAISY WEB PLAYER
// ---------------------------------------------------------------------------
// 
// (C) 2010-2021, Association pour le Bien des Aveugles
//                Place Bourg-de-Four 34
//                CH 1204 Geneva, Switzerland
//
//                Website : https://www.abage.ch
//                Email : aba@abage.ch
//                Phone : +41 22 317 79 19
// 
// Author :
//    Martin Mohnhaupt
//    Nice Data Systems
//    Route de la Plaine 90
//    CH 1283 La Plaine, Switzerland
//    Email : m.mohnhaupt@bluewin.ch
//    Website : http://www.nicedata.ch
//
// Uses : 
//    - jQuery library (JavaScript)
//    - Semantic UI (Styling)
//
// License : 
//     GPL, see http://www.gnu.org/licenses/gpl.html
// -----------------------------------------------------------------------------
import { Config, Loggers } from "./config.js";
import { I18N } from "./i18n.js";

(() => {
    /**
     * Version information.
     * Take care to update !
     */
    class VersionInfo {
        static program = 'Daisy Web Player';
        static major = 2;
        static minor = 4;
        static build = '2023-04-23';
        static copyright = '© 2010-2023, Association pour le Bien des Aveugles, Genève';
        static author = 'Martin Mohnhaupt, m.mohnhaupt@bluewin.ch';
        static license = 'GPL, see https://www.gnu.org/licenses/gpl-3.0.html';

        static version = `${this.major}.${this.minor}.${this.build}`;
        static version_string = `${this.program} | Version ${this.major}.${this.minor}.${this.build} | ${this.copyright}`;
        static mail_to_href = `mailto:aba@abage.ch?subject=Daisy player ${this.version}`;

        static as_html_table_body() {
            return '' +
                `<tr><td>Program</td><td>${VersionInfo.program}</td></tr>` +
                `<tr><td>Version</td><td>${VersionInfo.version}</td></tr>` +
                `<tr><td>Author</td><td>${VersionInfo.author}</td></tr>` +
                `<tr><td>Copyright</td><td>${VersionInfo.copyright}</td></tr>` +
                `<tr><td>License</td><td>${VersionInfo.license}</td></tr>` +
                `<tr><td>jQuery version</td><td>${$.fn.jquery}</td></tr>`;
        }
    }

    /**
     * Simple CORS proxying.
     * 
     * Since there may be CORS related exceptions on inter-site requests, this class provides
     * methods to redirect request through a proxy URL (typically a PHP script on the same server).
     */
    class CorsProxy {
        /**
         * Constructor
         * @param {string} proyx_url URL of the proxy (e.g. `https://dev.daisyplayer.ch/cors_proxy.php?url=`)
         */
        constructor(proyx_url = null) {
            this._proxy_url = proyx_url;
        }
        /**
         * Get the proxied URL.
         * @param {string} url 
         * @returns The proxied URL or the original one if no proxy is specified.
         */
        proxied_url(url) {
            return this._proxy_url ? this._proxy_url + url : url;
        }
    }

    /**
     * Utility methods
     * 
     * Needs class CorsProyx !
     */
    class Utils {
        static #cors_proxy = new CorsProxy(Config.CORS_PROXY_URL);

        /**
         * Get a base URL from a complete URL.
         * 
         * @param {string} url a full URL (e.g. `https://ww.daisyplayer.ch/project/nnn/ncc.html`)
         * @returns a base URL (in this example `https://ww.daisyplayer.ch/project/nnn/`)
         */
        static get_base_url(url) {
            const BASE_URL_REGEX = /(.*)[\/\\]([^\/\\]+)\.\w+$/;
            return url.match(BASE_URL_REGEX)[1] + '/';
        }

        /**
         * Truncates long strings and adds a `...` at its end.
         * @param {*} text The original string
         * @param {*} n Truncate after `n` characters
         * @returns The truncated string
         */
        static truncate(text, n = 35) {
            text = text.replace(/(\r\n|\n|\r)/gm, "");
            return (text.length > n) ? text.substr(0, n - 1) + '...' : text;
        }

        /**
         * Retrieve external XML files (async method).
         * 
         * @param {string} url The external resource URL
         * @returns An XML document or null
         */
        static fetch_xml = async (url) => {
            const CONTEXT = 'Utils.fetch_xml';
            const _logger = Loggers[CONTEXT] || Config.NO_LOGGER;
            const proxied_url = this.#cors_proxy.proxied_url(url);
            _logger(CONTEXT, `url=${url}, proxied_url=${proxied_url}`);
            const response = await fetch(proxied_url);
            _logger(CONTEXT, 'response', response);

            if (response.status != 200) {
                return null;
            }

            const data = await response.text();
            _logger(CONTEXT, `data size=${data.length}`);
            if (data.length == 0)
                return null;

            _logger(CONTEXT, 'Will return valid data !');
            return new DOMParser().parseFromString(data, "text/xml");
        };

        /**
         * Convert seconds to a formatted time string.
         * 
         * @param {*} timeInSeconds Time in seconds
         * @returns a string in the HH:MM:SS.mmm format
         * @author Jean van Kasteel (https://gist.github.com/vankasteelj)
         * @see https://gist.github.com/vankasteelj/74ab7793133f4b257ea3
         */
        static sec2time(timeInSeconds) {
            const pad = (num, size) => {
                return ('000' + num).slice(size * -1);
            };
            const time = parseFloat(timeInSeconds).toFixed(3);
            const hours = Math.floor(Number(time) / 60 / 60);
            const minutes = Math.floor(Number(time) / 60) % 60;
            const seconds = Math.floor(Number(time) - minutes * 60);
            const milliseconds = time.slice(-3);
            return pad(hours, 2) + ':' + pad(minutes, 2) + ':' + pad(seconds, 2) + '.' + pad(milliseconds, 3);
        }

        // static sec2time(timeInSeconds) {
        //     var pad = function (num, size) { return ('000' + num).slice(size * -1); },
        //         time = parseFloat(timeInSeconds).toFixed(3),
        //         hours = Math.floor(time / 60 / 60),
        //         minutes = Math.floor(time / 60) % 60,
        //         seconds = Math.floor(time - minutes * 60),
        //         milliseconds = time.slice(-3);
        //     return pad(hours, 2) + ':' + pad(minutes, 2) + ':' + pad(seconds, 2) + '.' + pad(milliseconds, 3);
        // }

        static throw_error_body(message) {
            const body_html = `<div class="ui container"><div class="dwp-error"><h1>${I18N.Tr('DWP_ERROR')}</h1><h2>${message}</h2></div></div>`;
            document.body.innerHTML = body_html;
        }

        /**
         * Generate an UID from an url and an identifier.
         * 
         * @param {String} url The URL
         * @param {String} identifier The identifier
         * @returns An identifier
         */
        static generate_uid(url, identifier) {
            return (encodeURI(url) + '_' + identifier).toString().toUpperCase().replaceAll('://', '_').replaceAll('/', '_').replaceAll('.', '_');
        }
    }
    /**
     * Deletes a bookmark from the `LocalStorage` ahd updates the GUI
     * 
     * @param {*} storage_key The `LocalStorage` key
     * @param {*} smil_index The stored smil index
     * @param {*} clip_index The stored clip index
     * @param {*} dom_id The DOM id of the bookmark (GUI)
     * @returns Nothing
     */
    class Bookmark {
        constructor(id, title, smil_index, clip_index) {
            const CONTEXT = 'Bookmark.constructor';
            this._logger = Loggers[this.constructor.name] || Config.NO_LOGGER;
            this._logger(CONTEXT, `New bookmark: title=${this.title}, smil_index=${this.smil_index}, clip_index=${this.clip_index}`);
            this.id = id;
            this.title = Utils.truncate(title, Config.BOOKMARK_TITLE_MAX_LENGTH || 30);
            this.smil_index = smil_index;
            this.clip_index = clip_index;
        }

        as_json() {
            return {
                'id': this.id,
                'title': this.title,
                'smil_index': this.smil_index,
                'clip_index': this.clip_index,
            }
        }

        as_html_list_item() {
            return `<li id="dwp-bookmark-${this.id}"><a href="#" data-bookmark_id="${this.id}" class="dwp-bookmark-link" title="${this.title}">${this.title}</a>` +
                `&nbsp;&nbsp;` +
                `<a class="dwp-bookmark-delete" href="#" data-bookmark_id="${this.id}" data-gui_element_id="dwp-bookmark-${this.id}" data-smil_index="${this.smil_index}" data-clip_index="${this.clip_index}" title="${I18N.Tr('BOOKMARK_DELETE')}">` +
                `<i class="orange window close outline icon"></i></a></li>`
        }
    }




    class Bookmarks {
        /**
        * 
        * @param {Ncc} ncc 
        */
        constructor(ncc) {
            const CONTEXT = 'Bookmarks.constructor';
            this._logger = Loggers[this.constructor.name] || Config.NO_LOGGER;
            this.title = ncc.title;
            this.ncc = ncc;
            this.items = [];
            this._logger(CONTEXT, `Creating bookmark container for Ncc: title=${this.title}, url=${this.ncc.uid}`);
            // Check if present in local storage
            this.load_from_localstorage();
        }


        /**
         * 
         * @param {Bookmark} bookmark 
         */
        create_and_add_bookmark(title, smil_index, clip_index,) {
            const CONTEXT = 'Bookmarks.add';
            this._logger(CONTEXT, 'Bookmark addition', title, smil_index, clip_index);
            this._logger(CONTEXT, 'Bookmarks', this.items);

            let item_found = false;
            this.items.forEach((item) => {
                if (item.smil_index === smil_index && item.clip_index === clip_index) {
                    item_found = true;
                    this._logger(CONTEXT, 'Bookmark addition failed (already exists)', title, smil_index, clip_index);
                    return;
                }
            });

            if (!item_found) {
                const bookmark = new Bookmark(this.items.length, title, smil_index, clip_index);
                this.items.push(bookmark);
                this._logger(CONTEXT, 'Bookmark added:', bookmark);
                Config.BOOKMARK_STORE.setItem(this.ncc.uid, this.as_json());
            }
        }

        delete_bookmark(bookmark_id) {
            let target_item = null;
            this.items.forEach((item) => {
                if (item.id == bookmark_id) {
                    target_item = item;
                    return;
                }
            });
            if (target_item) {
                const target_index = this.items.indexOf(target_item);
                this.items.splice(target_index, 1);
                if (this.items.length == 0)
                    Config.BOOKMARK_STORE.removeItem(this.ncc.uid)
                else
                    Config.BOOKMARK_STORE.setItem(this.ncc.uid, this.as_json());
            }
        }

        get_bookmark_by_id(bookmark_id) {
            let target_item = null;
            this.items.forEach((item) => {
                if (item.id == bookmark_id) {
                    target_item = item;
                    return;
                }
            });
            return target_item ? target_item : null;
        }


        as_json() {
            return JSON.stringify({
                'title': this.title,
                'bookmarks': this.items,
            });
        }

        as_html_list() {
            var result = '<ul>';
            $(this.items).each((_, item) => {
                result += item.as_html_list_item();
            })
            return result + '</ul>';
        }

        load_from_localstorage() {
            const CONTEXT = 'Bookmarks._load_from_localstorage';
            const stored = JSON.parse(Config.BOOKMARK_STORE.getItem(this.ncc.uid));
            if (!stored) {
                this._logger(CONTEXT, `${this.ncc.uid} object not found.`);
                return;
            }
            this.title = stored.title;
            this._logger(CONTEXT, `Found bookmarks for ${stored.title}.`);
            $(stored.bookmarks).each((_, item) => {
                this.create_and_add_bookmark(item.title, item.smil_index, item.clip_index, this.ncc.uid);
            })
        }

    }

    /**
    * This class represents a single GUI object.
    * Some jQuery methods are implemented to easily access/amend these elements.
    */
    class GuiElement {
        /**
         * Constructor
         * @param {string} id is the id of the html element (<tag id="id" ...>...</tag>) 
         * @param {string} title is an (optional) label that will set the 'title' property of the element
         * @param {string} content is the (optional) content that will set content of the element
         */
        constructor(id, title = null, content = null) {
            const CONTEXT = 'GuiElement.constructor';
            this._logger = Loggers[this.constructor.name] || Config.NO_LOGGER;
            this._logger(CONTEXT, `Creating [${id}], title=${title}, content=${content}`);
            this.id = id;
            this.title = title;
            this.content = content;
            this._jqo = $(`#${this.id}`); // Private jQuery object
            this._element = document.getElementById(this.id); // JS element

            if (!this._element) {
                this._logger(CONTEXT, `Element [${id}] not found in the DOM !`)
                return;
            }

            // Set the title attribute if available
            if (this.title) {
                this._element.title = this.title;
                this._element.ariaLabel = this.title;
            }
            // Set the content attribute if available
            if (this.content) {
                this._element.textContent = this.content;
            }
        }

        /**
         * Get the DOM object.
         * @returns {HTMLElement} the DOM element
         */
        as_document_element() {
            return document.getElementById(this.id);
        }

        /**
         * jQuery trigger() wrapper
         */
        trigger(event) {
            this._jqo.trigger(event);
            return this._jqo;
        }

        /**
         * jQuery hide() wrapper
         */
        hide() {
            this._jqo.hide();
            return this._jqo;
        }

        /**
         * jQuery show() wrapper
         */
        show() {
            this._jqo.show();
            return this._jqo;
        }

        /**
         * jQuery text(...) wrapper
         * @param {string} text the text to set
         */
        text(text) {
            this._jqo.text(text);
            return this._jqo;
        }

        /**
         * jQuery html(...) wrapper
         * @param {string} text a string (HTML code allowed) 
         */
        html(text) {
            this._jqo.html(text);
            return this._jqo;
        }

        /**
         * jQuery attr(name, value) wrapper
         * @param {string} name the attribute name
         * @param {*} value the attribute value
         */
        attr(name, value) {
            this._jqo.attr(name, value);
            return this._jqo;
        }

        set_title(value) {
            this._element.title = value;
            this._element.ariaLabel = value;
        }

        progress(percent) {
            percent = percent < 0 ? 0 : percent;
            percent = percent > 100 ? 100 : percent;
            this._jqo.progress({ percent: percent });
        }

        /**
         * Set an 'on click' handler
         * @param {function} handler 
         */
        on_click(handler) {
            this._jqo.bind('click', handler);
            return this._jqo;
        }

    }

    /**
     * This class represents the HTML elements available in the Web interface (GUI).
     */
    class DaisyPlayerGuiElements {
        /**
         * Create all GuiElements used by our application.
         */
        constructor() {
            // Audio player (<audio>)
            this.audio = new GuiElement('dwp-audio-player');
            // Text zone
            this.text_zone = new GuiElement('dwp-text-zone', I18N.Tr('TEXT_DISPLAY'));
            // Status zone
            this.status_zone = new GuiElement('dwp-status-zone');
            if (Config.SHOW_STATUS_ZONE === false)
                this.status_zone.hide();
            // Play progress bar
            this.play_progress = new GuiElement('dwp-play-progress');
            this.play_progress.progress(0);
            if (Config.SHOW_PLAY_PROGRESS === false)
                this.play_progress.hide();
            // Software info
            this.btn_player_bookmark = new GuiElement('dwp-player-bookmark', I18N.Tr('ADD_BOOKMARK'));
            // Tab & Menu headers            
            this.book_title = new GuiElement('dwp-book-title');
            this.menu_read = new GuiElement('dwp-menu-read', I18N.Tr('DISPLAY_READER'), I18N.Tr('READER'));
            this.menu_bookmarks = new GuiElement('dwp-menu-bookmarks', I18N.Tr('BOOKMARKS'), I18N.Tr('BOOKMARKS'));
            this.tab_bookmarks = new GuiElement('dwp-tab-bookmarks', null, I18N.Tr('BOOKMARKS'));
            this.menu_toc = new GuiElement('dwp-menu-toc', I18N.Tr('TOC'), I18N.Tr('TOC'));
            this.tab_toc = new GuiElement('dwp-tab-toc', null, I18N.Tr('TOC'));
            this.menu_details = new GuiElement('dwp-menu-details', I18N.Tr('METADATA_LONG'), I18N.Tr('METADATA'));
            this.tab_details = new GuiElement('dwp-tab-details', null, I18N.Tr('METADATA_LONG'));
            this.menu_settings = new GuiElement('dwp-menu-settings', I18N.Tr('SETTINGS'), I18N.Tr('SETTINGS'));
            this.tab_settings = new GuiElement('dwp-tab-settings', null, I18N.Tr('SETTINGS'));

            this.book_nav = new GuiElement('dwp-book-nav', I18N.Tr('BOOK_NAV'));
            this.audio_nav = new GuiElement('dwp-audio-nav', I18N.Tr('AUDIO_NAV'));
            this.various_nav = new GuiElement('dwp-various-nav', I18N.Tr('VARIOUS_NAV'));

            this.menu_infos = new GuiElement('dwp-menu-infos', 'Informations', 'Informations');

            this.th_property = new GuiElement('dwp-property', null, I18N.Tr('PROPERTY'));
            this.th_value = new GuiElement('dwp-value', null, I18N.Tr('VALUE'));
            this.th_scheme = new GuiElement('dwp-scheme', null, I18N.Tr('SCHEME'));
            this.th_position = new GuiElement('dwp-status-position', null, I18N.Tr('STATUS_POSITION'));
            this.th_duration = new GuiElement('dwp-status-duration', null, I18N.Tr('STATUS_DURATION'));
            this.th_volume = new GuiElement('dwp-status-volume', null, I18N.Tr('STATUS_VOLUME'));
            this.th_event = new GuiElement('dwp-status-event', null, I18N.Tr('STATUS_EVENT'));
            this.th_rate = new GuiElement('dwp-status-rate', null, I18N.Tr('STATUS_RATE'));
            this.th_req_level = new GuiElement('dwp-status-req-level', null, I18N.Tr('STATUS_REQ_LEVEL'));
            this.th_cur_level = new GuiElement('dwp-status-cur-level', null, I18N.Tr('STATUS_CUR_LEVEL'));




            // Audio player controls
            this.btn_clip_backward = new GuiElement('dwp-clip-backward', I18N.Tr('GO_BACK_5_SECS'));
            this.btn_play = new GuiElement('dwp-clip-play', I18N.Tr('PLAY'));
            this.btn_pause = new GuiElement('dwp-clip-pause', I18N.Tr('PAUSE'));
            this.btn_clip_forward = new GuiElement('dwp-clip-forward', I18N.Tr('GO_FWRD_5_SECS'));
            // Audio rate controls
            this.btn_play_normal = new GuiElement('dwp-play-normal', I18N.Tr('SPEED_1_0'));
            this.btn_play_faster = new GuiElement('dwp-play-faster', I18N.Tr('SPEED_1_5'));
            this.btn_play_fastest = new GuiElement('dwp-play-fastest', I18N.Tr('SPEED_2_0'));
            if (Config.SHOW_RATE_CONTROLS == false) {
                this.btn_play_normal.hide()
                this.btn_play_faster.hide()
                this.btn_play_fastest.hide()
            }
            // Audio volume controls
            this.btn_volume_up = new GuiElement('dwp-volume-up', I18N.Tr('VOLUME_INC'));
            this.btn_volume_down = new GuiElement('dwp-volume-down', I18N.Tr('VOLUME_DEC'));
            if (Config.SHOW_VOLUME_CONTROLS == false) {
                this.btn_volume_up.hide();
                this.btn_volume_down.hide();
            }
            // Book navigation controls
            this.btn_book_level_up = new GuiElement('dwp-book-level-up', I18N.Tr('NAV_LEVEL_INC'));
            this.btn_book_level_down = new GuiElement('dwp-book-level-down', I18N.Tr('NAV_LEVEL_DEC'));
            this.btn_book_forward = new GuiElement('dwp-book-forward', I18N.Tr('NAV_SECTION_NEXT'));
            this.btn_book_backward = new GuiElement('dwp-book-backward', I18N.Tr('NAV_SECTION_PREV'));
            // Infos 
            this.software_infos = new GuiElement('dwp-software-infos', null, I18N.Tr('SOFTWARE_INFOS'))
            this.clip_position = new GuiElement('dwp-clip-position', I18N.Tr('CLIP_CURRENT_POS'));
            this.clip_duration = new GuiElement('dwp-clip-duration', I18N.Tr('CLIP_DURATION'));
            this.clip_volume = new GuiElement('dwp-clip-volume', I18N.Tr('PLAYBACK_VOLUME'));
            this.clip_event = new GuiElement('dwp-clip-event', I18N.Tr('AUDIO_EVENT_LATEST'));
            this.clip_rate = new GuiElement('dwp-clip-rate', I18N.Tr('PLAYBACK_RATE'));
            this.navigation_level = new GuiElement('dwp-nav-level-cur', I18N.Tr('NAV_LEVEL_CURRENT'));
            this.current_level = new GuiElement('dwp-nav-level', I18N.Tr('NAV_LEVEL'));
            //
            this.clip_source = new GuiElement('dwp-audio-source', null);
            this.page_footer = new GuiElement('dwp-footer', null);
            this.mail_to = new GuiElement('dwp-mail-to', null);
            // TOC 
            this.toc = new GuiElement('dwp-toc', I18N.Tr('TOC'));
            // Menu
            // Dynamic
            this.meta_table_body = new GuiElement('dwp-meta-table-body');
            this.info_info_body = new GuiElement('dwp-info-table-body');
            this.bookmark_list = new GuiElement('dwp-bookmarks');
            this.main_ui = new GuiElement('dwp-full-ui');
        }
    }

    /**
     * This class represents an event fired by the HTML5 audio element.
     * This class is also used tou count the specific event (counter property)
     */
    class DaisyPlayerEvent {
        /**
         * Constructor
         * @param {string} name the event name (e.g. 'canplay', 'playing', ...)
         */
        constructor(name) {
            this.name = name;
            this.count = 0;
        }
    }

    /**
     * This class is a collection of all available audio player events.
     */
    class DaisyPlayerEvents {
        // static EVENT_NAMES = ['loadstart', 'progress', 'suspend', 'abort', 'error', 'emptied', 'stalled', 'loadedmetadata',
        //     'loadeddata', 'canplay', 'canplaythrough', 'playing', 'waiting', 'seeking', 'seeked', 'ended', 'durationchange',
        //     'timeupdate', 'play', 'pause', 'ratechange', 'resize', 'volumechange', 'paused'];
        /**
         * Constructor
         */
        constructor() {
            this.items = [];
            for (const [key, value] of Object.entries(PlayerEvents)) {
                this.items.push(new DaisyPlayerEvent(value))
            }
        }
        /**
         * Search by name for a specific `DaisyPlayerEvent` element, and increment the event count.
         * @param {string} name Event name 
         * @returns {DaisyPlayerEvent} the `DaisyPlayerEvent` event (or null)
         */
        get_event_and_increment_count(name) {
            const item = this.items.find(item => item.name === name);
            if (item)
                item.count++;
            else
                console.error('Unknown player event: ' + name);
            return item ? item : null;
        }
    }

    /**
     * GUI events to handle.
     */
    const GuiEvents = Object.freeze({
        CLIP_START: 0,
        CLIP_BACKWARD: 1,
        CLIP_FORWARD: 2,
        CLIP_END: 3,
        CLIP_PLAY: 4,
        CLIP_PAUSE: 5,
        CLIP_PLAY_FASTEST: 6,
        CLIP_PLAY_NORMAL: 7,
        CLIP_PLAY_FASTER: 8,
        VOLUME_UP: 9,
        VOLUME_DOWN: 10,
        BOOK_FIRST_SECTION: 11,
        BOOK_PREV_SECTION: 12,
        BOOK_NEXT_SECTION: 13,
        BOOK_LAST_SECTION: 14,
        BOOK_LEVEL_UP: 15,
        BOOK_LEVEL_DOWN: 16,
        BOOKMARK: 17,
    });

    /**
     * Book sections
     */
    const BookSections = Object.freeze({
        FIRST: 0,
        NEXT: 1,
        PREVIOUS: 2,
        LAST: 3,
        SPECIFIC: 4,
    });

    const PlayerEvents = Object.freeze({
        ABORT: 'abort',
        CANPLAY: 'canplay',
        CANPLAYTHROUGH: 'canplaythrough',
        DURATIONCHANGE: 'durationchange',
        EMPTIED: 'emptied',
        ENDED: 'ended',
        ERROR: 'error',
        LOADEDDATA: 'loadeddata',
        LOADEDMETADATA: 'loadedmetadata',
        LOADSTART: 'loadstart',
        PAUSE: 'pause',
        PAUSED: 'paused',
        PLAY: 'play',
        PLAYING: 'playing',
        PROGRESS: 'progress',
        RATECHANGE: 'ratechange',
        RESIZE: 'resize',
        STALLED: 'stalled',
        SEEKED: 'seeked',
        SEEKING: 'seeking',
        SUSPEND: 'suspend',
        TIMEUPDATE: 'timeupdate',
        VOLUMECHANGE: 'volumechange',
        WAITING: 'waiting',
    });


    /**
     * This class is the machinery to :
     * - load a Daisy 2.02 NCC file
     * - play the audio clips
     * - display the audio related text
     * - handle the GUI user interactions (audio control, book navigation)
     * - handle the audio events
     */
    class DaisyPlayer {
        /**
         * `DaisyPlayer` object constructor
         * @param {Ncc} ncc 
         * @returns and instance of the `DaisyPlayer` class
         */
        constructor(ncc, autoplay) {
            const CONTEXT = `${this.constructor.name}.constructor`;
            // Logging
            this._logger = Loggers[this.constructor.name] || Config.NO_LOGGER;
            this._logger(CONTEXT, 'DaisyPlayer init called...');


            // Initialize the class members
            this.ncc = ncc;
            this.autoplay = autoplay;
            this.valid_ncc = ncc && ncc.is_valid;
            if (!this.valid_ncc) {
                Utils.throw_error_body(`The Daisy player cannot work with an invalid NCC !`);
                return;
            }
            // Bookmarks container
            this.bookmarks = new Bookmarks(this.ncc);
            // Class members
            this.navigation_level = ncc.min_level;
            this.playback_rate = Config.INITIAL_RATE;
            this.volume = Config.INITIAL_VOLUME;
            this.current_smil = null; // Current SMIL    
            this.clip_index = 0; // Index -> clips
            this.current_clip = null; // Clip under treatment
            this.current_media_url = null; // Used to avoid media reload. See method set_media(url)

            this.last_section_index = this.ncc.smil_references[this.ncc.smil_references.length - 1].section_index;
            this.end_of_book_reached = false;

            this.toc_link_class = 'dwp-toc-link';
            this.bookmark_link_class = 'dwp-bookmark-link'

            // Attach the GUI elements
            this.gui_elements = new DaisyPlayerGuiElements();
            this.player = this.gui_elements.audio.as_document_element();
            this.events = new DaisyPlayerEvents();
            // Populate the GUI elements 
            this.gui_elements.btn_pause.hide();
            this.gui_elements.clip_duration.text(Utils.sec2time(0));
            this.gui_elements.clip_position.text(Utils.sec2time(0));
            this.gui_elements.page_footer.html(VersionInfo.version_string);
            this.gui_elements.navigation_level.text(this.navigation_level);
            this.gui_elements.current_level.text(this.navigation_level);
            this.gui_elements.clip_rate.text(this.playback_rate);
            this.gui_elements.clip_volume.text(Number(this.volume * 100.0).toFixed(0));
            this.gui_elements.info_info_body.html(VersionInfo.as_html_table_body);
            this.gui_elements.mail_to.as_document_element().href = VersionInfo.mail_to_href;

            // Attach the media event handler
            this.events.items.forEach(item => {
                this.player.addEventListener(item.name, item => this.media_event_handler(item));
            });

            // Attach the GUI player event handlers  
            this.gui_elements.btn_pause.on_click(() => this.on_click(GuiEvents.CLIP_PAUSE));
            this.gui_elements.btn_play.on_click(() => this.on_click(GuiEvents.CLIP_PLAY));
            this.gui_elements.btn_clip_backward.on_click(() => this.on_click(GuiEvents.CLIP_BACKWARD));
            this.gui_elements.btn_clip_forward.on_click(() => this.on_click(GuiEvents.CLIP_FORWARD));
            // Volume
            this.gui_elements.btn_volume_up.on_click(() => this.on_click(GuiEvents.VOLUME_UP));
            this.gui_elements.btn_volume_down.on_click(() => this.on_click(GuiEvents.VOLUME_DOWN));
            // Playrate
            this.gui_elements.btn_play_normal.on_click(() => this.on_click(GuiEvents.CLIP_PLAY_NORMAL));
            this.gui_elements.btn_play_faster.on_click(() => this.on_click(GuiEvents.CLIP_PLAY_FASTER));
            this.gui_elements.btn_play_fastest.on_click(() => this.on_click(GuiEvents.CLIP_PLAY_FASTEST));
            // Attach the GUI book navigation event handlers  
            this.gui_elements.btn_book_backward.on_click(() => this.on_click(GuiEvents.BOOK_PREV_SECTION));
            this.gui_elements.btn_book_forward.on_click(() => this.on_click(GuiEvents.BOOK_NEXT_SECTION));
            this.gui_elements.btn_book_level_up.on_click(() => this.on_click(GuiEvents.BOOK_LEVEL_UP));;
            this.gui_elements.btn_book_level_down.on_click(() => this.on_click(GuiEvents.BOOK_LEVEL_DOWN));
            // Bookmark current state
            this.gui_elements.btn_player_bookmark.on_click(() => this.on_click(GuiEvents.BOOKMARK));
            // Bookmarks
            this.populate_boomarks();
            // Top menu handler
            this.build_top_menu_handler()
            // Set the document title (tab) and document title on the page 
            document.title = ncc.title;
            this.gui_elements.book_title.text(ncc.title);
            // Build the TOC
            this.populate_toc_page();
            // Meta data page
            this.populate_metainfo_page()

            // Start playing at the beginning of book
            //MM this.navigate_to(BookSections.FIRST);
            //MM this.play_section(BookSections.FIRST);


            this.gui_elements.main_ui.show();
            // TODO: Attach keayboard events
            this._logger(CONTEXT, 'DaisyPlayer init done.');
        }

        build_top_menu_handler() {
            $('#dwp-page-menu .item').bind('click', (source) => {
                const _source = $(source.target);
                const page_to_show = '#' + _source.attr('data-target');
                $('#dwp-page-menu').children().removeClass('active');
                _source.addClass('active');
                $('.dwp-page').hide();
                $(page_to_show).show();
            });
        }

        populate_metainfo_page() {
            var table_body = '';
            this.ncc.meta_tags.forEach(meta => {
                table_body += meta.as_table_row();

            });
            this.gui_elements.meta_table_body.html(table_body);
        }

        /**
         * Delete a bookmark
         * 
         * @param {*} smil_index The SMIL index
         * @param {*} clip_index The CLIP index
         * @param {*} gui_element_id The related GUI element
         */
        delete_bookmark(bookmark_id, gui_element_id) {
            this.bookmarks.delete_bookmark(bookmark_id);
            document.getElementById(gui_element_id).remove();
        }

        populate_boomarks() {
            this.gui_elements.bookmark_list.html(this.bookmarks.as_html_list());

            // Update play links
            $('.' + this.bookmark_link_class).bind('click', async source => {
                const bookmark = this.bookmarks.get_bookmark_by_id(source.target.dataset.bookmark_id);
                this.current_smil = await this.ncc.get_smil_by_section_index(bookmark.smil_index);
                this.section_index = bookmark.smil_index;
                this.clip_index = bookmark.clip_index;
                this.play_clip();
                this.gui_elements.menu_read.trigger('click');
            });

            // Update delete links
            Array.from(document.getElementsByClassName("dwp-bookmark-delete")).forEach(element => {
                element.addEventListener('click', () => {
                    this.delete_bookmark(element.dataset.bookmark_id, element.dataset.gui_element_id);
                });
            });

        }

        populate_toc_page() {
            // Content
            var toc = '';
            var currentLevel, previousLevel;
            $(this.ncc.smil_references).each((index, item) => {
                if (item.level === 0)
                    return;
                currentLevel = item.level;
                const toc_item = `<li><a data-target="${item.section_index}" href="#" class="${this.toc_link_class}" title="${item.label}">${item.label}</a></li>\n`;
                if (currentLevel === previousLevel) { // Put a leaf
                    toc += toc_item;
                } else if (currentLevel > previousLevel) { // Open a new branch and put a leaf
                    if (currentLevel - previousLevel !== 1) {
                        // NIX
                    }
                    // Remove le last </li>\n
                    toc = toc.replace(/<\/li>\n$/, '\n');
                    // And begin a new list
                    toc += '<ul class="dwp-toc">' + toc_item;
                } else if (currentLevel < previousLevel) { // Close the branch, then put a leaf
                    if (previousLevel - currentLevel !== 1) {
                        // NIX
                    }
                    // Close unmatched lists / listitems
                    for (var i = previousLevel - currentLevel; i !== 0; i--) {
                        toc += '</ul>\n</li>\n';
                    }
                    toc += toc_item;
                }
                previousLevel = currentLevel;
            });
            this.gui_elements.toc.html(toc);

            // Click event handlers
            $('.' + this.toc_link_class).bind('click', source => {
                const target_section_index = parseInt($(source.target).attr('data-target'), 10);
                this.navigate_to(BookSections.SPECIFIC, target_section_index);
                this.play_section(BookSections.FIRST);
                this.gui_elements.menu_read.trigger('click');
            })
        }

        navigate_to(section, index = null) {
            const CONTEXT = `${this.constructor.name}.navigate_to`;
            this._logger(CONTEXT, `BookSection: ${section}, Navigation level: ${this.navigation_level}, Index: ${index}`);
            var smil_reference = null;
            switch (section) {
                case BookSections.SPECIFIC:
                    smil_reference = this.ncc.smil_section_references[index];
                    this._logger(CONTEXT, 'Found specific:', smil_reference, smil_reference.id === index);
                    this.section_index = index;
                    break;
                case BookSections.FIRST:
                    for (let i = 0; i <= this.ncc.max_section_index(); i++) {
                        smil_reference = this.ncc.smil_section_references[i];
                        if (smil_reference.level === this.navigation_level) {
                            this._logger(CONTEXT, 'Found first:', smil_reference);
                            this.section_index = i;
                            break;
                        }
                    }
                    break;
                case BookSections.NEXT:
                    for (let i = this.section_index + 1; i <= this.ncc.max_section_index(); i++) {
                        smil_reference = this.ncc.smil_section_references[i];
                        if (smil_reference.level === this.navigation_level) {
                            this._logger(CONTEXT, 'Found next:', smil_reference);
                            this.section_index = i;
                            break;
                        }
                    }
                    break;
                case BookSections.PREVIOUS:
                    for (let i = this.section_index - 1; i >= 0; i--) {
                        smil_reference = this.ncc.smil_section_references[i];
                        if (smil_reference.level === this.navigation_level) {
                            this._logger(CONTEXT, 'Found prev:', smil_reference);
                            this.section_index = i;
                            break;
                        }
                    }
                    break;
                case BookSections.LAST:
                    for (let i = this.ncc.max_section_index(); i >= 0; i--) {
                        smil_reference = this.ncc.smil_section_references[i];
                        if (smil_reference.level === this.navigation_level) {
                            this._logger(CONTEXT, 'Found last:', smil_reference);
                            this.section_index = i;
                            break;
                        }
                    }
                    break;
            }
        }

        /**
         * Play a book section
         * @param {BookSections} section Book section to play
         */
        async play_section(section) {
            const CONTEXT = `${this.constructor.name}.play_section`;
            this._logger(CONTEXT, `BookSection: ${section}, Navigation level: ${this.navigation_level}`);
            switch (section) {
                case BookSections.NEXT:
                    this.section_index = (this.section_index + 1 <= this.ncc.max_section_index()) ? this.section_index + 1 : this.section_index;
                    break;
                case BookSections.FIRST:
                case BookSections.LAST:
                case BookSections.PREVIOUS:
                    break;
            }
            this._logger(CONTEXT, 'Current section index : ', this.section_index);
            const section_reference = this.ncc.smil_section_references[this.section_index];
            this._logger(CONTEXT, 'Next section reference : ', section_reference);
            this.current_smil = await this.ncc.get_smil_by_section_index(section_reference.id);
            this._logger(CONTEXT, 'Smil : ', this.current_smil);
            this.gui_elements.current_level.text(section_reference.level);
            this.clip_index = 0;
            this.play_clip();
        }

        play_clip() {
            // Get the current clip from the clip list
            const clip = this.current_smil.clips[this.clip_index];

            if (clip === undefined) {
                this.player.pause();
                if (this.section_index == this.last_section_index && this.clip_index >= this.current_smil.last_clip_index)
                    this.end_of_book_reached = true;
                return;
            }
            this.current_clip = clip;
            // Get the current phrase
            this.gui_elements.text_zone.text(this.current_clip.text);
            this.gui_elements.text_zone.set_title(this.current_clip.text);

            // Get the audio source
            this.set_media(this.current_clip.media_url);
            this.player.volume = this.volume;
            // Set the player start
            this.player.currentTime = this.current_clip.start_sec;
            // Start the html5 player !
            if (this.autoplay) {
                // document.getElementById("dwp-clip-play").click();
                // let play_promise = this.player.play();
                // if (play_promise !== undefined) {
                //     play_promise.then(_ => {
                //         // Automatic playback started!
                //         // Show playing UI.
                //     }).catch(error => {
                //         // Auto-play was prevented
                //         // Show paused UI.
                //     });
                // }
            }
        }

        /**
         * Load a new media.
         * 
         * @param {string} url is the URL of the media lo load
         */
        set_media(url) {
            const CONTEXT = `${this.constructor.name}.set_media`;
            // Check whether this audio file is already loaded
            if (url === this.current_media_url) {
                this._logger(CONTEXT, '<' + url + '> is already loaded.');
                return;
            }
            // Set the new media
            this.player.pause();
            this.current_media_url = url;
            this.gui_elements.clip_source.attr('src', this.current_media_url);
            // Load it !
            this.player.load();
        }

        /**
         * GUI click interactions handler.
         * @param {number} gui_event the GUI event as defined in `GuiEvents`
         */
        on_click(gui_event) {
            const CONTEXT = `${this.constructor.name}.on_click`;
            this._logger(`${CONTEXT} GUI event: ${gui_event}`);
            // Handle GUI events    
            switch (gui_event) {
                // Audio related events
                case GuiEvents.CLIP_START:
                    this.player.currentTime = this.current_clip.start_sec;
                    break;
                case GuiEvents.CLIP_BACKWARD:
                    if (this.player.currentTime - 5 > this.current_clip.start_sec) {
                        this.player.currentTime -= 5;
                    }
                    break;
                case GuiEvents.CLIP_FORWARD:
                    if (this.player.currentTime + 5 < this.current_clip.end_sec) {
                        this.player.currentTime += 5;
                    }
                    break;
                case GuiEvents.CLIP_END:
                    this.player.currentTime = this.current_clip.end_sec;
                    break;
                case GuiEvents.CLIP_PLAY:
                    this.player.play();
                    break;
                case GuiEvents.CLIP_PAUSE:
                    this.player.pause();
                    break;
                case GuiEvents.CLIP_PLAY_FASTER:
                    this.playback_rate = 1.5;
                    this.player.playbackRate = this.playback_rate;
                    this.gui_elements.clip_rate.text(this.playback_rate);
                    break;
                case GuiEvents.CLIP_PLAY_FASTEST:
                    this.playback_rate = 2.0;
                    this.player.playbackRate = this.playback_rate;
                    this.gui_elements.clip_rate.text(this.playback_rate);
                    break;
                case GuiEvents.CLIP_PLAY_NORMAL:
                    this.playback_rate = 1.0;
                    this.player.playbackRate = this.playback_rate;
                    this.gui_elements.clip_rate.text(this.playback_rate);
                    break;
                // Navigation related events
                case GuiEvents.BOOK_FIRST_SECTION:
                    this.navigate_to(BookSections.FIRST);
                    this.play_section(BookSections.FIRST);
                    break;
                case GuiEvents.BOOK_NEXT_SECTION:
                    this.navigate_to(BookSections.NEXT);
                    this.play_section(BookSections.FIRST);
                    break;
                case GuiEvents.BOOK_PREV_SECTION:
                    this.navigate_to(BookSections.PREVIOUS);
                    this.play_section(BookSections.FIRST);
                    break;
                case GuiEvents.BOOK_LAST_SECTION:
                    this.navigate_to(BookSections.LAST);
                    this.play_section(BookSections.FIRST);
                    break;
                case GuiEvents.BOOK_LEVEL_UP:
                    if (this.navigation_level + 1 < this.ncc.max_level)
                        this.navigation_level++;
                    this.gui_elements.navigation_level.text(this.navigation_level);
                    this.navigate_to(BookSections.FIRST);
                    this.play_section(BookSections.FIRST);
                    break;
                case GuiEvents.BOOK_LEVEL_DOWN:
                    if (this.navigation_level - 1 >= this.ncc.min_level)
                        this.navigation_level--;
                    this.gui_elements.navigation_level.text(this.navigation_level);
                    this.navigate_to(BookSections.FIRST);
                    this.play_section(BookSections.FIRST);
                    break;
                case GuiEvents.VOLUME_UP:
                    if (this.volume + 0.1 < 1)
                        this.volume += 0.1;
                    this.player.volume = this.volume;
                    this.gui_elements.clip_volume.text(Number(this.volume * 100.0).toFixed(0));
                    break;
                case GuiEvents.VOLUME_DOWN:
                    if (this.volume - 0.1 > 0)
                        this.volume -= 0.1;
                    this.player.volume = this.volume;
                    this.gui_elements.clip_volume.text(Number(this.volume * 100.0).toFixed(0));
                    break;
                case GuiEvents.BOOKMARK:
                    this.bookmarks.create_and_add_bookmark(this.current_clip.text, this.section_index, this.current_clip.index);
                    // Refresh
                    this.populate_boomarks();
                    break;
                default:
                    break;
            }
        }

        /**
         * Audio media event handler. 
         * @param {Event} event media event
         * @see `DaisyPlayerEvents`
         */
        media_event_handler(event) {
            const CONTEXT = `${this.constructor.name}.media_event_handler`;
            this._logger(CONTEXT, 'Event: ' + event.type);

            // Update event counters
            const media_event = this.events.get_event_and_increment_count(event.type);
            this.gui_elements.clip_event.text(event.type);
            // Update UI items
            switch (media_event.name) {
                case PlayerEvents.ABORT:
                    this.gui_elements.btn_play.show();
                    this.gui_elements.btn_pause.hide();
                    break;
                case PlayerEvents.PLAY:
                    this.gui_elements.btn_play.hide();
                    this.gui_elements.btn_pause.show();
                    break;
                case PlayerEvents.PAUSE:
                    // When clip is paused and current position is after the expected clip end...
                    if (this.player.currentTime >= this.current_clip.end_sec) {
                        this.play_section(BookSections.NEXT);
                    }
                    this.gui_elements.btn_play.show();
                    this.gui_elements.btn_pause.hide();
                    break;
                case PlayerEvents.DURATIONCHANGE:
                    this.gui_elements.clip_duration.text(Utils.sec2time(this.player.duration));
                    break;
                case PlayerEvents.TIMEUPDATE:// 'timeupdate':
                    const ct = this.player.currentTime;
                    // Compute the play progress (clip_position / clip_duration) in percent
                    const progress_pc = ((ct - this.current_clip.start_sec) / (this.current_clip.end_sec - this.current_clip.start_sec)) * 100;

                    this.gui_elements.clip_position.text(Utils.sec2time(ct));
                    this.gui_elements.play_progress.progress(progress_pc);

                    if ((this.player.currentTime >= this.current_clip.end_sec)) {
                        this.player.pause();
                        this.clip_index++;
                        this.play_clip();
                    }
                    break;
                case PlayerEvents.ENDED:
                    this.player.currentTime = this.current_clip.start_sec;
                    this.gui_elements.clip_position.text(Utils.sec2time(this.player.currentTime));
                    // End of book ?
                    if (this.section_index == this.ncc.max_section_index()) { // YES !
                        this.player.pause();
                    } else { // NO !
                        this.play_section(BookSections.NEXT);
                    }
                    break;
                case PlayerEvents.CANPLAY:
                    this.gui_elements.clip_position.text(Utils.sec2time(this.player.currentTime));
                    // Reset the playback rate since it can be lost
                    this.player.playbackRate = this.playback_rate;
                    this.player.volume = this.volume;
                    break;
                case PlayerEvents.CANPLAYTHROUGH:
                    if (this.autoplay && !this.end_of_book_reached) {
                        const play_promise = this.player.play();
                        if (play_promise !== undefined) {
                            play_promise.then(_ => {
                                // NIX
                            }).catch(error => {
                                // NIX
                            });
                        }
                    } else {
                        this.end_of_book_reached = false;
                    }
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * This class represents the meta information found in the various files of a Daisy project.
     */
    class Meta {
        /**
         * Construction of a Meta element.
         * 
         * @param {*} name attribute name (name=...)
         * @param {*} content the value of the attribute (content=...)
         * @param {*} scheme the value scheme (optional)
         */
        constructor(name, content, scheme) {
            this.name = name;
            this.content = content;
            this.scheme = scheme ? scheme : null;
        }
        /**
         * @returns A table row (`<tr>...</tr>`)
         */
        as_table_row() {
            return `<tr><td>${this.name}</td><td>${this.content}</td><td>${this.scheme ? this.scheme : ''}</td></tr>`;
        }
    }

    class DaisySectionType {
        static UNDEFINED = 0;
        static SECTION = 1;
        static PAGE = 2;
        static NOTEREF = 3;
        static PRODNOTE = 4;
    }

    /**
     * This class represents the SMIL items referenced from the NCC file.
     */
    class SmilReference {
        constructor(id, type, level, section_index, href, anchor, label) {
            const CONTEXT = `${this.constructor.name}.constructor`;
            this._logger = Loggers[this.constructor.name] || Config.NO_LOGGER;
            this._logger(CONTEXT, `id=${id}, type=${type}, level=${level}, section_index=${section_index}, href=${href}, anchor=${anchor}, label=${Utils.truncate(label)}`);
            this.id = id;
            this.type = type;
            this.level = level;
            this.section_index = section_index;
            this.href = href;
            this.anchor = anchor;
            this.label = label;
        }
    }

    class SmilSectionReference {
        constructor(id, level, index, smil_index) {
            this.id = id;
            this.level = level;
            this.index = index;
            this.smil_index = smil_index;
        }
    }

    class FullTextDocument {
        constructor(url, source) {
            this.url = url;
            this.base_url = Utils.get_base_url(url);
            this.source = source;
            this.is_valid = this.source !== null;
            this.fulltext_html = null;
            this.process();
        }

        process() {
            if (!this.is_valid)
                return;
            // Remove special styles
            $('*', this.source).each((index, item) => {
                $(item).removeAttr('style').removeAttr('align').removeAttr('valign');
            });
            // Massage some tags (remove them, but keep content)
            $('a, em, strong, q, i, big', this.source).contents().unwrap().wrap('');
            // Update the image tags to point to the right source
            $('img', this.source).each((index, item) => {
                $(item).attr('src', this.base_url + $(item).attr('src'));
            });
            // Page numbering
            $('.page-normal', this.source).each((index, item) => {
                $(item).text('- ' + $(item).text() + ' -');
            });
            this.fulltext_html = $('body', this.source);
        }
    }

    /**
     * This is a container for 
     */
    class FullTextDocumentContainer {
        constructor() {
            this.documents = [];
        }

        add(fulltext_document) {
            this.documents.push(fulltext_document);
        }

        count() {
            return this.documents.length;
        }

        has_been_processed(url) {
            return this.documents.find(item => item.url === url) ? true : false;
        }

        get(url) {
            return this.documents.find(item => item.url === url);
        }

    }

    class Clip {
        constructor(index, media_url, text_anchor, start_sec, end_sec, text) {
            this.index = index;
            this.media_url = media_url;
            this.text_anchor = text_anchor;
            this.start_sec = start_sec;
            this.end_sec = end_sec;
            this.text = text.replaceAll('\n', ' ');
        }
    }

    /**
     * This class represents a SMIL file referenced by a href="xxxxx.smil#anchor" in the NCC file.
     */
    class Smil {
        /**
         * Constuctor
         * @param {Ncc} ncc The parent Ncc instance
         * @param {string} href The referenced url (e.g. `<a href="002.smil#rgn_txt_0002_0001">...</a>`)
         */
        constructor(ncc, source, href) {
            const CONTEXT = `${this.constructor.name}.constructor`;
            this._logger = Loggers[this.constructor.name] || Config.NO_LOGGER;
            this.parent = ncc;
            this.url = href;
            this.base_url = Utils.get_base_url(this.url);
            this._logger(CONTEXT, `ncc: ${ncc.url}, href: ${href}, base_url: ${this.base_url}`);
            this.source = source;
            this.is_valid = this.source !== null;
            this.fulltext_url = null;
            this.clips = Array();
            this.pars = $('par', this.source);
            this.last_clip_index = this.clips.length - 1;
        }

        async process() {
            const CONTEXT = `${this.constructor.name}.process`;
            if (!this.is_valid)
                return;
            // Use the first <par> block to retrieve full text (if any)
            const par = this.pars[0];
            // Get the full text location
            this.fulltext_url = this.base_url + $('text', par).attr('src').split('#')[0];
            // If already processed, do nothing
            if (this.parent.fulltext_document_container.has_been_processed(this.fulltext_url)) {
                this._logger(CONTEXT, 'SMIL <' + this.fulltext_url + '> was already processed !');
                return;
            }
            // Retrieve the full text
            const fulltext_document = await Factories.create_full_text_document(this.fulltext_url);
            this.parent.fulltext_document_container.add(fulltext_document);
        }

        process_clips() {
            if (!this.is_valid)
                return;
            $(this.pars).each((index, item) => {
                // const clip_id = `par-${index}`;
                const text_anchor = $.escapeSelector($('text:first', item).attr('src').split('#')[1]);
                // Get the text frament for this <par/>
                const fulltext_document = this.parent.fulltext_document_container.get(this.fulltext_url);
                const fragment = $.trim($('#' + text_anchor, fulltext_document.fulltext_html).text());
                const first = $('audio:first', item);
                const last = $('audio:last', item);
                const clip_url = this.base_url + first.attr('src')
                const clip_start = first.attr('clip-begin').replace(/[^0-9]+/g, '') / 1000.0;
                const clip_end = last.attr('clip-end').replace(/[^0-9]+/g, '') / 1000.0;
                const clip = new Clip(index, clip_url, text_anchor, clip_start, clip_end, fragment);
                this.clips.push(clip);
            });
        }
    }

    class Ncc {
        #base_url = '';
        #source = null;
        is_valid = false;
        url = ''
        title = '';
        uid = '';
        max_level = 0;
        min_level = 999;
        meta_tags = [];
        smil_references = [];
        smil_section_references = [];
        fulltext_document_container = new FullTextDocumentContainer();

        constructor(xml_source, source_url) {
            const CONTEXT = `${this.constructor.name}.constructor`;
            this._logger = Loggers[this.constructor.name] || Config.NO_LOGGER;
            this._logger(CONTEXT, 'Building an NCC class instance...')

            // We have a good NCC if there is a Dublin Core title (dc:title)
            const dc_title = xml_source.querySelector('meta[name="dc:title"]');
            this.is_valid = dc_title ? true : false;

            if (!this.is_valid)
                return;

            this.url = source_url;
            this.#source = xml_source;
            this.#base_url = Utils.get_base_url(source_url);

            // Get displayed title from the source document
            const source_title = this.#source.body.querySelector('h1.title');
            this.title = source_title ? source_title.textContent : 'NO_TITLE';

            // Build an UID (used for bookmarking)
            const dc_identifier = this.#source.head.querySelector('meta[name="dc:identifier"]');
            this.uid = Utils.generate_uid(source_url, (dc_identifier ? dc_identifier.content : ''));

            // Get the meta tags
            this.#source.head.querySelectorAll('meta').forEach(item => {
                if (item.name)
                    this.meta_tags.push(new Meta(item.name, item.content, item.scheme));
            });

            // Process the <h1> to <h6> and <span> tags
            let index = 0, section_index = 0;
            this.#source.body.querySelectorAll('h1, h2, h3, h4, h5, h6, span').forEach(item => {
                const tag_name = item.nodeName, class_name = item.classList[0] || null;
                let type = DaisySectionType.UNDEFINED, level = 0;
                // Spans may have a special meaning depending on the class attribute
                if (tag_name === 'span') {
                    switch (class_name) {
                        case 'page-normal':
                            type = DaisySectionType.PAGE;
                            break;
                        case 'optional-prodnote':
                            type = DaisySectionType.PRODNOTE;
                            break;
                        case 'page-noteref':
                            type = DaisySectionType.NOTEREF;
                            break;
                        default:
                            break;
                    }
                } else {
                    type = DaisySectionType.SECTION;
                    level = parseInt(tag_name[1], 10);
                    level > this.max_level ? this.max_level = level : {};
                    level < this.min_level ? this.min_level = level : {};
                }

                // Build the SMIL reference
                // To save bandwith the actual SMIL file is not loaded.
                // This will be done by the client application (Daisy Web Player)
                const link = item.querySelector('a');
                const [smil_name, smil_anchor] = $(link).get(0).href.split('#');
                const smil_url = this.#base_url + smil_name.split('/').pop();
                const smil_label = link.textContent;
                const smil_reference = new SmilReference(index, type, level, section_index, smil_url, smil_anchor, smil_label);
                this.smil_references.push(smil_reference);

                // Build a Smil section entry
                if (type === DaisySectionType.SECTION) {
                    // const smil_section = new SmilSectionReference(index, level, section_index++);
                    const smil_reference = new SmilReference(section_index, type, level, null, smil_url, smil_anchor, smil_label);
                    // this.smil_section_references.push(smil_section);
                    this.smil_section_references.push(smil_reference);
                    section_index++;
                }
                index++;
            });
        }
        max_section_index() {
            return this.smil_section_references.length - 1;
        }
        /**
        * Get a smil by a section index
        * @param {number} index 
        * @returns 
        */
        async get_smil_by_section_index(index) {
            const CONTEXT = `${this.constructor.name}.get_smil_by_section_index`;
            this._logger(CONTEXT, `Looking for smil by index ${index}/${this.smil_section_references.length - 1}`);
            // Check limits
            if (index < 0 || index >= this.smil_section_references.length)
                return null;

            const smil_section_reference = this.smil_section_references[index];
            this._logger(CONTEXT, 'smil_reference:', smil_section_reference);
            return await Factories.create_smil(this, smil_section_reference.href);
        }

    }

    class Factories {
        /**
         * Create a new `Ncc`.
         * 
         * @param {*} url The URL where the NCC can be found.
         * 
         * @returns A brand new `Ncc` instance.
         */
        static async create_ncc(url) {
            const source = await Utils.fetch_xml(url);
            return new Ncc(source, url)
        }

        static async create_smil(ncc, href) {
            const source = await Utils.fetch_xml(href);
            const smil = new Smil(ncc, source, href);
            await smil.process();
            smil.process_clips();
            return smil;
        }

        // static async full_text_document_builder(url) {
        static async create_full_text_document(url) {
            console.log(url);
            const source = await Utils.fetch_xml(url);
            console.log(source);
            return new FullTextDocument(url, source);
        }
    }


    /**
     * This class represents the Daisy root file (ncc.html).
     */
    // class xNcc {
    //     /**
    //      * Constructor
    //      * @param {xml} xml_source The XML Ncc source (XML)
    //      */
    //     constructor(xml_source, url) {
    //         const CONTEXT = `${this.constructor.name}.constructor`;
    //         this._logger = Loggers[this.constructor.name] || Config.NO_LOGGER;
    //         this._logger(CONTEXT, 'Building an NCC class instance...')

    //         this.url = url;
    //         this.base_url = Utils.get_base_url(url);
    //         this.source = xml_source;


    //         // We have a good NCC if there is a Dublin Core title (dc:title)
    //         const dc_title = this.source.querySelector('meta[name="dc:title"]');
    //         this.is_valid = dc_title ? true : false;

    //         // Get displayed title from the source document
    //         const source_title = this.source.querySelector('h1.title');
    //         this.title = source_title ? source_title.textContent : 'NO_TITLE';

    //         // Build an UID (used for bookmarking)
    //         const dc_identifier = this.source.querySelector('meta[name="dc:identifier"]');
    //         const identifier = (dc_identifier ? dc_identifier.content : '');
    //         this.uid = Utils.generate_uid(this.url, identifier);

    //         this.min_level = 6
    //         this.max_level = 0
    //         this.meta_tags = new Array();
    //         this.smil_references = new Array(); // All smils references
    //         this.smil_section_references = Array(); // Smils referencing sections
    //         this.fulltext_document_container = new FullTextDocumentContainer();
    //         this.current_smil_index = 0;
    //         this.current_section_index = 0;
    //         this.process();
    //         this._logger(CONTEXT, 'Done.')
    //     }

    //     /**
    //     * Build a new Ncc instance (factory).
    //     *
    //     *  @param {string} url URL of the NCC file (Daisy index) 
    //     *  @returns {Ncc} a new Ncc instance
    //     *
    //     */
    //     static async new(ncc_url) {
    //         const source = await Utils.fetch_xml(ncc_url);
    //         if (!source) {
    //             const message = I18N.Tr('INVALID_NCC').replace(I18N.parameter_1, ncc_url)
    //             Utils.throw_error_body(message);
    //             return null;
    //         }
    //         return new Ncc(source, ncc_url);
    //     }

    //     max_section_index() {
    //         return this.smil_section_references.length - 1;
    //     }

    //     /**
    //      * Get a smil by a section index
    //      * @param {number} index 
    //      * @returns 
    //      */
    //     async get_smil_by_section_index(index) {
    //         const CONTEXT = `${this.constructor.name}.get_smil_by_section_index`;
    //         this._logger(CONTEXT, `Looking for smil by index ${index}/${this.smil_section_references.length - 1}`);
    //         // Check limits
    //         if (index < 0 || index >= this.smil_section_references.length)
    //             return null;

    //         const smil_section_reference = this.smil_section_references[index];
    //         this._logger(CONTEXT, 'smil_reference:', smil_section_reference);
    //         return await Smil.smil_builder(this, smil_section_reference.href);
    //     }

    //     /**
    //      * Process the ncc file.
    //      * @returns 
    //      */
    //     process() {
    //         const CONTEXT = `${this.constructor.name}.process`;
    //         this._logger(CONTEXT, 'Processing the NCC document...');
    //         if (!this.is_valid)
    //             return;
    //         var section_index = 0;

    //         // Get the meta tags
    //         $('meta*[name]', this.source).map((index, tag) => tag = $(tag)).each((index, tag) => {
    //             this.meta_tags.push(new Meta(tag.attr('name'), tag.attr('content'), tag.attr('scheme')));
    //         });

    //         // Process the h1-h6 and span tags
    //         $('h1, h2, h3, h4, h5, h6, span', this.source).map((index, tag) => tag = $(tag)).each((index, tag) => {
    //             const tag_name = tag.get(0).nodeName;
    //             var type = DaisySectionType.UNDEFINED;
    //             var level = 0;
    //             // Spans may have a special meaning depending on the class attribute
    //             if (tag_name === 'span') {
    //                 switch (tag.attr('class')) {
    //                     case 'page-normal':
    //                         type = DaisySectionType.PAGE;
    //                         break;
    //                     case 'optional-prodnote':
    //                         type = DaisySectionType.PRODNOTE;
    //                         break;
    //                     case 'page-noteref':
    //                         type = DaisySectionType.NOTEREF;
    //                         break;
    //                     default:
    //                         break;
    //                 }
    //             } else {
    //                 type = DaisySectionType.SECTION;
    //                 level = parseInt(tag_name[1], 10);
    //                 level > this.max_level ? this.max_level = level : {};
    //                 level < this.min_level ? this.min_level = level : {};
    //             }

    //             // Build the SMIL reference
    //             // To save bandwith the actual SMIL file is not loaded.
    //             // This will be done by the client application (Daisy Web Player)
    //             const link = tag.find('a');
    //             const [smil_name, smil_anchor] = $(link).get(0).href.split('#');
    //             const smil_url = this.base_url + smil_name.split('/').pop();
    //             const smil_label = $(link).text();
    //             this._logger(CONTEXT, `SMIL reference from <${tag_name}>: id=${index}, url=${smil_url}, anchor=${smil_anchor}`);
    //             const smil_reference = new SmilReference(index, type, level, section_index, smil_url, smil_anchor, smil_label);
    //             this.smil_references.push(smil_reference);

    //             // Build a Smil section entry
    //             if (type === DaisySectionType.SECTION) {
    //                 // const smil_section = new SmilSectionReference(index, level, section_index++);
    //                 const smil_reference = new SmilReference(section_index, type, level, -1, smil_url, smil_anchor, smil_label);
    //                 // this.smil_section_references.push(smil_section);
    //                 this.smil_section_references.push(smil_reference);
    //                 section_index++;
    //             }
    //         });
    //         this._logger(CONTEXT, 'Done.');
    //     }
    // }

    /**
    * Analyze the ids and classes used for DWP
    */
    function analyze_html() {
        const CONTEXT = `analyze_html`;
        const _logger = Loggers[CONTEXT] || Config.NO_LOGGER;

        const _header = '=============[ HTML Analyzer ]=============';
        _logger(CONTEXT, _header);
        // Ids
        _logger(CONTEXT, 'Phase 1 (ID attributes)');
        _logger(CONTEXT, '=======================');
        $('*[id]', window.document).each((index, element) => {
            _logger(CONTEXT, `ID [${element.id}] identifies a <${element.localName}> element. ${element.title}`);
        });
        // Classes
        _logger(CONTEXT, 'Phase 2 (classes)');
        _logger(CONTEXT, '=================');
        $('*[class]', window.document).each((index, element) => {
            const clazz = $(element).attr('class').split(' ');
            $(clazz).each((index, value) => {
                if (value.startsWith('dwp'))
                    _logger(CONTEXT, `${value} -> ${element.localName}, id=${element.id}, target: ${$(element).attr('data-target') || '-'}`);
            })
        });
        _logger(CONTEXT, _header);
    }

    /**
     * This is the program entry point.
     * 
     * It should be when the document is ready.
     * @returns 
     */
    async function main() {
        const CONTEXT = `DaisyWebPlayer.main`;
        const _logger = Loggers[CONTEXT] || Config.NO_LOGGER;
        _logger(CONTEXT, 'function started.');


        // Handle the URL parameters
        const url_parameters = new URLSearchParams(window.location.search);

        // Langage (&lang=...)
        const lang = url_parameters.get('lang') || Config.DEFAULT_LANGAGE;
        $('html').attr('lang', lang); // Set  <html lang='..'>
        I18N.set_lang(lang);

        // NCC url (&ncc=...)
        const ncc_url = url_parameters.get('ncc');
        if (!ncc_url) { // FATAL !
            Utils.throw_error_body(I18N.Tr('NO_NCC'));
            return;
        }

        // Autoplay (&autoplay)
        let autoplay = url_parameters.get('autoplay') || null;
        autoplay = autoplay ? autoplay === 'true' || autoplay === '1' : Config.AUTO_PLAY;

        // Eventually analyze the HTML template    
        if (Config.DEBUG)
            analyze_html();

        // Build the Ncc class instance
        const ncc = await Factories.create_ncc(ncc_url);
        // Check if valid
        if (!ncc.is_valid) {
            const message = I18N.Tr('INVALID_NCC').replace(I18N.parameter_1, ncc.url)
            Utils.throw_error_body(message);
            return;
        }



        // Create the player
        const player = new DaisyPlayer(ncc, autoplay);
        player.navigate_to(BookSections.FIRST);
        await player.play_section(BookSections.FIRST);

        _logger(CONTEXT, 'function ended.');
    }

    //==============================================================
    // Run the main() function when the document is ready...
    document.addEventListener('DOMContentLoaded', async () => {
        await main();
    });
    //==============================================================
})();