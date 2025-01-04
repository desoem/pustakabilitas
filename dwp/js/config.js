import { I18N } from "./i18n.js";

export const Config = Object.freeze({
    DEBUG: false,
    DEFAULT_LANGAGE: I18N.ID,
    CORS_PROXY_URL: './cors_proxy.php?url=', // Bypass CORS
    AUTO_PLAY: true,
    SHOW_VOLUME_CONTROLS: false,
    SHOW_RATE_CONTROLS: true,
    SHOW_STATUS_ZONE: false,
    SHOW_PLAY_PROGRESS: false,
    INITIAL_VOLUME: 80 / 100,
    INITIAL_RATE: 1,
    BOOKMARK_TITLE_MAX_LENGTH: 80,
    BOOKMARK_STORE: window.localStorage,
});

export const LogTo = Object.freeze({
    NO_LOGGER: (...args) => { }, // No log at all
    CONSOLE: (...args) => { console.log(args) }, // Log to console
});

export const Loggers = Object.freeze({
    'Utils.fetch_xml': LogTo.NO_LOGGER,
    'Bookmark': LogTo.NO_LOGGER,
    'Bookmarks': LogTo.NO_LOGGER,
    'GuiElement': LogTo.NO_LOGGER,
    'DaisyPlayer.constructor': LogTo.NO_LOGGER,
    'DaisyPlayer.navigate_to': LogTo.NO_LOGGER,
    'SmilReference': LogTo.NO_LOGGER,
    'Smil': LogTo.NO_LOGGER,
    'Clip': LogTo.NO_LOGGER,
    'Ncc': LogTo.NO_LOGGER,
    'analyze_html': LogTo.NO_LOGGER,
    'DaisyWebPlayer.main': LogTo.NO_LOGGER,
});


