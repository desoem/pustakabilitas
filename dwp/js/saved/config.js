import { I18N } from "./i18n.js";

// Logging methods
const NO_LOGGER = () => { }; // No log at all
const CONSOLE = console.log; // Log to console

export const Config = Object.freeze({
    DEBUG: false,
    DEFAULT_LANGAGE: I18N.ID,
    CORS_PROXY_URL: './cors_proxy.php?url=', // Bypass CORS
    AUTO_PLAY: true,
    SHOW_VOLUME_CONTROLS: false,
    SHOW_RATE_CONTROLS: true,
    SHOW_STATUS_ZONE: true,
    SHOW_PLAY_PROGRESS: false,
    INITIAL_VOLUME: 80 / 100,
    INITIAL_RATE: 1,
    BOOKMARK_TITLE_MAX_LENGTH: 80,
    BOOKMARK_STORE: window.localStorage,
    NO_LOGGER: () => { }, // No log at all
    CONSOLE: console.log, // Log to console
});

export const Loggers = Object.freeze({
    'Utils.fetch_xml': NO_LOGGER,
    'Bookmark': NO_LOGGER,
    'Bookmarks': NO_LOGGER,
    'GuiElement': NO_LOGGER,
    'DaisyPlayer': NO_LOGGER,
    'SmilReference': NO_LOGGER,
    'Smil': NO_LOGGER,
    'Ncc': NO_LOGGER,
    'analyze_html': NO_LOGGER,
    'DaisyWebPlayer.main': NO_LOGGER,
});


