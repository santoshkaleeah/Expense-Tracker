YUI.add('moodle-core-widget-focusafterclose', function (Y, NAME) {

/**
 * Provides support for focusing on different nodes after the Widget is
 * hidden.
 *
 * If the focusOnPreviousTargetAfterHide attribute is true, then the module hooks
 * into the show function for that Widget to try and determine which Node
 * caused the Widget to be shown.
 *
 * Alternatively, the focusAfterHide attribute can be passed a Node.
 *
 * @module moodle-core-widget-focusafterhide
 */

var CAN_RECEIVE_FOCUS_SELECTOR = 'input:not([type="hidden"]), ' +
                                 'a[href], button, textarea, select, ' +
                                '[tabindex], [contenteditable="true"]';

/**
 * Provides support for focusing on different nodes after the Widget is
 * hidden.
 *
 * @class M.core.WidgetFocusAfterHide
 */
function WidgetFocusAfterHide() {
    Y.after(this._bindUIFocusAfterHide, this, 'bindUI');
    if (this.get('rendered')) {
        this._bindUIFocusAfterHide();
    }
}

WidgetFocusAfterHide.ATTRS = {
    /**
     * Whether to focus on the target that caused the Widget to be shown.
     *
     * <em>If this is true, and a valid Node is found, any Node specified to focusAfterHide
     * will be ignored.</em>
     *
     * @attribute focusOnPreviousTargetAfterHide
     * @default false
     * @type boolean
     */
    focusOnPreviousTargetAfterHide: {
        value: false
    },

    /**
     * The Node to focus on after hiding the Widget.
     *
     * <em>Note: If focusOnPreviousTargetAfterHide is true, and a valid Node is found, then this
     * value will be ignored. If it is true and not found, then this value will be used as
     * a fallback.</em>
     *
     * @attribute focusAfterHide
     * @default null
     * @type Node
     */
    focusAfterHide: {
        value: null,
        type: Y.Node
    }
};

WidgetFocusAfterHide.prototype = {
    /**
     * The list of Event Handles which we should cancel when the dialogue is destroyed.
     *
     * @property uiHandleFocusAfterHide
     * @type array
     * @protected
     */
    _uiHandlesFocusAfterHide: [],

    /**
     * A reference to the real show method which is being overwritten.
     *
     * @property _showFocusAfterHide
     * @type function
     * @default null
     * @protected
     */
    _showFocusAfterHide: null,

    /**
     * A reference to the detected previous target.
     *
     * @property _previousTargetFocusAfterHide
     * @type function
     * @default null
     * @protected
     */
    _previousTargetFocusAfterHide: null,

    initializer: function() {

        if (this.get('focusOnPreviousTargetAfterHide') && this.show) {
            // Overwrite the parent method so that we can get the focused
            // target.
            this._showFocusAfterHide = this.show;
            this.show = function(e) {
                this._showFocusAfterHide.apply(this, arguments);

                // We use a property rather than overriding the focusAfterHide parameter in
                // case the target cannot be found at hide time.
                this._previousTargetFocusAfterHide = null;
                if (e && e.currentTarget) {
                    Y.log("Determined a Node which caused the Widget to be shown",
                            'debug', 'moodle-core-widget-focusafterhide');
                    this._previousTargetFocusAfterHide = e.currentTarget;
                }
            };
        }
    },

    destructor: function() {
        new Y.EventHandle(this.uiHandleFocusAfterHide).detach();
    },

    /**
     * Set up the event handling required for this module to work.
     *
     * @method _bindUIFocusAfterHide
     * @private
     */
    _bindUIFocusAfterHide: function() {
        // Detach the old handles first.
        new Y.EventHandle(this.uiHandleFocusAfterHide).detach();
        this.uiHandleFocusAfterHide = [
            this.after('visibleChange', this._afterHostVisibleChangeFocusAfterHide)
        ];
    },

    /**
     * Handle the change in UI visibility.
     *
     * This method changes the focus after the hide has taken place.
     *
     * @method _afterHostVisibleChangeFocusAfterHide
     * @private
     */
    _afterHostVisibleChangeFocusAfterHide: function() {
        if (!this.get('visible')) {
            if (this._attemptFocus(this._previousTargetFocusAfterHide)) {
                Y.log("Focusing on the target automatically determined when the Widget was opened",
                        'debug', 'moodle-core-widget-focusafterhide');

            } else if (this._attemptFocus(this.get('focusAfterHide'))) {
                // Fall back to the focusAfterHide value if one was specified.
                Y.log("Focusing on the target provided to focusAfterHide",
                        'debug', 'moodle-core-widget-focusafterhide');

            } else {
                Y.log("No valid focus target found - not returning focus.",
                        'debug', 'moodle-core-widget-focusafterhide');

            }
        }
    },

    _attemptFocus: function(node) {
        var focusTarget = Y.one(node);
        if (focusTarget) {
            focusTarget = focusTarget.ancestor(CAN_RECEIVE_FOCUS_SELECTOR, true);
            if (focusTarget) {
                focusTarget.focus();
                return true;
            }
        }
        return false;
    }
};

var NS = Y.namespace('M.core');
NS.WidgetFocusAfterHide = WidgetFocusAfterHide;


}, '@VERSION@', {"requires": ["base-build", "widget"]});
/*
YUI 3.17.2 (build 9c3c78e)
Copyright 2014 Yahoo! Inc. All rights reserved.
Licensed under the BSD License.
http://yuilibrary.com/license/
*/

YUI.add('plugin', function (Y, NAME) {

    /**
     * Provides the base Plugin class, which plugin developers should extend, when creating custom plugins
     *
     * @module plugin
     */

    /**
     * The base class for all Plugin instances.
     *
     * @class Plugin.Base
     * @extends Base
     * @param {Object} config Configuration object with property name/value pairs.
     */
    function Plugin(config) {
        if (! (this.hasImpl && this.hasImpl(Y.Plugin.Base)) ) {
            Plugin.superclass.constructor.apply(this, arguments);
        } else {
            Plugin.prototype.initializer.apply(this, arguments);
        }
    }

    /**
     * Object defining the set of attributes supported by the Plugin.Base class
     *
     * @property ATTRS
     * @type Object
     * @static
     */
    Plugin.ATTRS = {

        /**
         * The plugin's host object.
         *
         * @attribute host
         * @writeonce
         * @type Plugin.Host
         */
        host : {
            writeOnce: true
        }
    };

    /**
     * The string identifying the Plugin.Base class. Plugins extending
     * Plugin.Base should set their own NAME value.
     *
     * @property NAME
     * @type String
     * @static
     */
    Plugin.NAME = 'plugin';

    /**
     * The name of the property the the plugin will be attached to
     * when plugged into a Plugin Host. Plugins extending Plugin.Base,
     * should set their own NS value.
     *
     * @property NS
     * @type String
     * @static
     */
    Plugin.NS = 'plugin';

    Y.extend(Plugin, Y.Base, {

        /**
         * The list of event handles for event listeners or AOP injected methods
         * applied by the plugin to the host object.
         *
         * @property _handles
         * @private
         * @type Array
         * @value null
         */
        _handles: null,

        /**
         * Initializer lifecycle implementation.
         *
         * @method initializer
         * @param {Object} config Configuration object with property name/value pairs.
         */
        initializer : function(config) {
            this._handles = [];
        },

        /**
         * Destructor lifecycle implementation.
         *
         * Removes any event listeners or injected methods applied by the Plugin
         *
         * @method destructor
         */
        destructor: function() {
            // remove all handles
            if (this._handles) {
                for (var i = 0, l = this._handles.length; i < l; i++) {
                   this._handles[i].detach();
                }
            }
        },

        /**
         * Listens for the "on" moment of events fired by the host,
         * or injects code "before" a given method on the host.
         *
         * @method doBefore
         *
         * @param strMethod {String} The event to listen for, or method to inject logic before.
         * @param fn {Function} The handler function. For events, the "on" moment listener. For methods, the function to execute before the given method is executed.
         * @param context {Object} An optional context to call the handler with. The default context is the plugin instance.
         * @return handle {EventHandle} The detach handle for the handler.
         */
        doBefore: function(strMethod, fn, context) {
            var host = this.get("host"), handle;

            if (strMethod in host) { // method
                handle = this.beforeHostMethod(strMethod, fn, context);
            } else if (host.on) { // event
                handle = this.onHostEvent(strMethod, fn, context);
            }

            return handle;
        },

        /**
         * Listens for the "after" moment of events fired by the host,
         * or injects code "after" a given method on the host.
         *
         * @method doAfter
         *
         * @param strMethod {String} The event to listen for, or method to inject logic after.
         * @param fn {Function} The handler function. For events, the "after" moment listener. For methods, the function to execute after the given method is executed.
         * @param context {Object} An optional context to call the handler with. The default context is the plugin instance.
         * @return handle {EventHandle} The detach handle for the listener.
         */
        doAfter: function(strMethod, fn, context) {
            var host = this.get("host"), handle;

            if (strMethod in host) { // method
                handle = this.afterHostMethod(strMethod, fn, context);
            } else if (host.after) { // event
                handle = this.afterHostEvent(strMethod, fn, context);
            }

            return handle;
        },

        /**
         * Listens for the "on" moment of events fired by the host object.
         *
         * Listeners attached through this method will be detached when the plugin is unplugged.
         *
         * @method onHostEvent
         * @param {String | Object} type The event type.
         * @param {Function} fn The listener.
         * @param {Object} context The execution context. Defaults to the plugin instance.
         * @return handle {EventHandle} The detach handle for the listener.
         */
        onHostEvent : function(type, fn, context) {
            var handle = this.get("host").on(type, fn, context || this);
            this._handles.push(handle);
            return handle;
        },

        /**
         * Listens for the "on" moment of events fired by the host object one time only.
         * The listener is immediately detached when it is executed.
         *
         * Listeners attached through this method will be detached when the plugin is unplugged.
         *
         * @method onceHostEvent
         * @param {String | Object} type The event type.
         * @param {Function} fn The listener.
         * @param {Object} context The execution context. Defaults to the plugin instance.
         * @return handle {EventHandle} The detach handle for the listener.
         */
        onceHostEvent : function(type, fn, context) {
            var handle = this.get("host").once(type, fn, context || this);
            this._handles.push(handle);
            return handle;
        },

        /**
         * Listens for the "after" moment of events fired by the host object.
         *
         * Listeners attached through this method will be detached when the plugin is unplugged.
         *
         * @method afterHostEvent
         * @param {String | Object} type The event type.
         * @param {Function} fn The listener.
         * @param {Object} context The execution context. Defaults to the plugin instance.
         * @return handle {EventHandle} The detach handle for the listener.
         */
        afterHostEvent : function(type, fn, context) {
            var handle = this.get("host").after(type, fn, context || this);
            this._handles.push(handle);
            return handle;
        },

        /**
         * Listens for the "after" moment of events fired by the host object one time only.
         * The listener is immediately detached when it is executed.
         *
         * Listeners attached through this method will be detached when the plugin is unplugged.
         *
         * @method onceAfterHostEvent
         * @param {String | Object} type The event type.
         * @param {Function} fn The listener.
         * @param {Object} context The execution context. Defaults to the plugin instance.
         * @return handle {EventHandle} The detach handle for the listener.
         */
        onceAfterHostEvent : function(type, fn, context) {
            var handle = this.get("host").onceAfter(type, fn, context || this);
            this._handles.push(handle);
            return handle;
        },

        /**
         * Injects a function to be executed before a given method on host object.
         *
         * The function will be detached when the plugin is unplugged.
         *
         * @method beforeHostMethod
         * @param {String} method The name of the method to inject the function before.
         * @param {Function} fn The function to inject.
         * @param {Object} context The execution context. Defaults to the plugin instance.
         * @return handle {EventHandle} The detach handle for the injected function.
         */
        beforeHostMethod : function(strMethod, fn, context) {
            var handle = Y.Do.before(fn, this.get("host"), strMethod, context || this);
            this._handles.push(handle);
            return handle;
        },

        /**
         * Injects a function to be executed after a given method on host object.
         *
         * The function will be detached when the plugin is unplugged.
         *
         * @method afterHostMethod
         * @param {String} method The name of the method to inject the function after.
         * @param {Function} fn The function to inject.
         * @param {Object} context The execution context. Defaults to the plugin instance.
         * @return handle {EventHandle} The detach handle for the injected function.
         */
        afterHostMethod : function(strMethod, fn, context) {
            var handle = Y.Do.after(fn, this.get("host"), strMethod, context || this);
            this._handles.push(handle);
            return handle;
        },

        toString: function() {
            return this.constructor.NAME + '[' + this.constructor.NS + ']';
        }
    });

    Y.namespace("Plugin").Base = Plugin;


}, '3.17.2', {"requires": ["base-base"]});
YUI.add('moodle-core-lockscroll', function (Y, NAME) {

/**
 * Provides the ability to lock the scroll for a page, allowing nested
 * locking.
 *
 * @module moodle-core-lockscroll
 */

/**
 * Provides the ability to lock the scroll for a page.
 *
 * This is achieved by applying the class 'lockscroll' to the body Node.
 *
 * Nested widgets are also supported and the scroll lock is only removed
 * when the final plugin instance is disabled.
 *
 * @class M.core.LockScroll
 * @extends Plugin.Base
 */
Y.namespace('M.core').LockScroll = Y.Base.create('lockScroll', Y.Plugin.Base, [], {

    /**
     * Whether the LockScroll has been activated.
     *
     * @property _enabled
     * @type Boolean
     * @protected
     */
    _enabled: false,

    /**
     * Handle destruction of the lockScroll instance, including disabling
     * of the current instance.
     *
     * @method destructor
     */
    destructor: function() {
        this.disableScrollLock();
    },

    /**
     * Start locking the page scroll.
     *
     * This is achieved by applying the lockscroll class to the body Node.
     *
     * A count of the total number of active, and enabled, lockscroll instances is also kept on
     * the body to ensure that premature disabling does not occur.
     *
     * @method enableScrollLock
     * @param {Boolean} forceOnSmallWindow Whether to enable the scroll lock, even for small window sizes.
     * @chainable
     */
    enableScrollLock: function(forceOnSmallWindow) {
        if (this.isActive()) {
            Y.log('LockScroll already active. Ignoring enable request', 'warn', 'moodle-core-lockscroll');
            return;
        }

        if (!this.shouldLockScroll(forceOnSmallWindow)) {
            Y.log('Dialogue height greater than window height. Ignoring enable request.', 'warn', 'moodle-core-lockscroll');
            return;
        }

        Y.log('Enabling LockScroll.', 'debug', 'moodle-core-lockscroll');
        this._enabled = true;
        var body = Y.one(Y.config.doc.body);

        // Get width of body before turning on lockscroll.
        var widthBefore = body.getComputedStyle('width');

        // We use a CSS class on the body to handle the actual locking.
        body.addClass('lockscroll');

        // Increase the count of active instances - this is used to ensure that we do not
        // remove the locking when parent windows are still open.
        // Note: We cannot use getData here because data attributes are sandboxed to the instance that created them.
        var currentCount = parseInt(body.getAttribute('data-activeScrollLocks'), 10) || 0,
            newCount = currentCount + 1;
        body.setAttribute('data-activeScrollLocks', newCount);
        Y.log("Setting the activeScrollLocks count from " + currentCount + " to " + newCount,
                'debug', 'moodle-core-lockscroll');

        // When initially enabled, set the body max-width to its current width. This
        // avoids centered elements jumping because the width changes when scrollbars
        // disappear.
        if (currentCount === 0) {
            body.setStyle('maxWidth', widthBefore);
        }

        return this;
    },

    /**
     * Recalculate whether lock scrolling should be on or off.
     *
     * @method shouldLockScroll
     * @param {Boolean} forceOnSmallWindow Whether to enable the scroll lock, even for small window sizes.
     * @return boolean
     */
    shouldLockScroll: function(forceOnSmallWindow) {
        var dialogueHeight = this.get('host').get('boundingBox').get('region').height,
            // Most modern browsers use win.innerHeight, but some older versions of IE use documentElement.clientHeight.
            // We fall back to 0 if neither can be found which has the effect of disabling scroll locking.
            windowHeight = Y.config.win.innerHeight || Y.config.doc.documentElement.clientHeight || 0;

        if (!forceOnSmallWindow && dialogueHeight > (windowHeight - 10)) {
            return false;
        } else {
            return true;
        }
    },

    /**
     * Recalculate whether lock scrolling should be on or off because the size of the dialogue changed.
     *
     * @method updateScrollLock
     * @param {Boolean} forceOnSmallWindow Whether to enable the scroll lock, even for small window sizes.
     * @chainable
     */
    updateScrollLock: function(forceOnSmallWindow) {
        // Both these functions already check if scroll lock is active and do the right thing.
        if (this.shouldLockScroll(forceOnSmallWindow)) {
            this.enableScrollLock(forceOnSmallWindow);
        } else {
            this.disableScrollLock(true);
        }

        return this;
    },

    /**
     * Stop locking the page scroll.
     *
     * The instance may be disabled but the scroll lock not removed if other instances of the
     * plugin are also active.
     *
     * @method disableScrollLock
     * @chainable
     */
    disableScrollLock: function(force) {
        if (this.isActive()) {
            Y.log('Disabling LockScroll.', 'debug', 'moodle-core-lockscroll');
            this._enabled = false;

            var body = Y.one(Y.config.doc.body);

            // Decrease the count of active instances.
            // Note: We cannot use getData here because data attributes are sandboxed to the instance that created them.
            var currentCount = parseInt(body.getAttribute('data-activeScrollLocks'), 10) || 1,
                newCount = currentCount - 1;

            if (force || currentCount === 1) {
                body.removeClass('lockscroll');
                body.setStyle('maxWidth', null);
            }

            body.setAttribute('data-activeScrollLocks', currentCount - 1);
            Y.log("Setting the activeScrollLocks count from " + currentCount + " to " + newCount,
                    'debug', 'moodle-core-lockscroll');
        }

        return this;
    },

    /**
     * Return whether scroll locking is active.
     *
     * @method isActive
     * @return Boolean
     */
    isActive: function() {
        return this._enabled;
    }

}, {
    NS: 'lockScroll',
    ATTRS: {
    }
});


}, '@VERSION@', {"requires": ["plugin", "base-build"]});
YUI.add('moodle-core-notification-dialogue', function (Y, NAME) {

/* eslint-disable no-unused-vars, no-unused-expressions */
var DIALOGUE_PREFIX,
    BASE,
    CONFIRMYES,
    CONFIRMNO,
    TITLE,
    QUESTION,
    CSS;

DIALOGUE_PREFIX = 'moodle-dialogue',
BASE = 'notificationBase',
CONFIRMYES = 'yesLabel',
CONFIRMNO = 'noLabel',
TITLE = 'title',
QUESTION = 'question',
CSS = {
    BASE: 'moodle-dialogue-base',
    WRAP: 'moodle-dialogue-wrap',
    HEADER: 'moodle-dialogue-hd',
    BODY: 'moodle-dialogue-bd',
    CONTENT: 'moodle-dialogue-content',
    FOOTER: 'moodle-dialogue-ft',
    HIDDEN: 'hidden',
    LIGHTBOX: 'moodle-dialogue-lightbox'
};

// Set up the namespace once.
M.core = M.core || {};
/* global DIALOGUE_PREFIX, BASE */

/**
 * The generic dialogue class for use in Moodle.
 *
 * @module moodle-core-notification
 * @submodule moodle-core-notification-dialogue
 */

var DIALOGUE_NAME = 'Moodle dialogue',
    DIALOGUE,
    DIALOGUE_FULLSCREEN_CLASS = DIALOGUE_PREFIX + '-fullscreen',
    DIALOGUE_HIDDEN_CLASS = DIALOGUE_PREFIX + '-hidden',
    DIALOGUE_SELECTOR = ' [role=dialog]',
    MENUBAR_SELECTOR = '[role=menubar]',
    DOT = '.',
    HAS_ZINDEX = 'moodle-has-zindex',
    CAN_RECEIVE_FOCUS_SELECTOR = 'input:not([type="hidden"]), a[href], button, textarea, select, [tabindex]',
    FORM_SELECTOR = 'form';

/**
 * A re-usable dialogue box with Moodle classes applied.
 *
 * @param {Object} c Object literal specifying the dialogue configuration properties.
 * @constructor
 * @class M.core.dialogue
 * @extends Panel
 */
DIALOGUE = function(config) {
    // The code below is a hack to add the custom content node to the DOM, on the fly, per-instantiation and to assign the value
    // of 'srcNode' to this newly created node. Normally (see docs: https://yuilibrary.com/yui/docs/widget/widget-extend.html),
    // this node would be pre-existing in the DOM, and an id string would simply be passed in as a property of the config object
    // during widget instantiation, however, because we're creating it on the fly (and 'config.srcNode' isn't set yet), care must
    // be taken to add it to the DOM and to properly set the value of 'config.srcNode' before calling the parent constructor.
    // Note: additional classes can be added to this content node by setting the 'additionalBaseClass' config property (a string).
    var id = 'moodle-dialogue-' + Y.stamp(this); // Can't use this.get('id') as it's not set at this stage.
    config.notificationBase =
        Y.Node.create('<div class="' + CSS.BASE + '">')
              .append(Y.Node.create('<div id="' + id + '" role="dialog" ' +
                                    'aria-labelledby="' + id + '-header-text" class="' + CSS.WRAP + '"  aria-live="polite"></div>')
              .append(Y.Node.create('<div id="' + id + '-header-text" class="' + CSS.HEADER + ' yui3-widget-hd"></div>'))
              .append(Y.Node.create('<div class="' + CSS.BODY + ' yui3-widget-bd"></div>'))
              .append(Y.Node.create('<div class="' + CSS.FOOTER + ' yui3-widget-ft"></div>')));
    Y.one(document.body).append(config.notificationBase);
    config.srcNode = '#' + id;
    delete config.buttons; // Don't let anyone pass in buttons as we want to control these during init. addButton can be used later.
    DIALOGUE.superclass.constructor.apply(this, [config]);
};
Y.extend(DIALOGUE, Y.Panel, {
    // Window resize event listener.
    _resizeevent: null,
    // Orientation change event listener.
    _orientationevent: null,
    _calculatedzindex: false,
    // Current maskNode id
    _currentMaskNodeId: null,
    /**
     * The original position of the dialogue before it was reposition to
     * avoid browser jumping.
     *
     * @property _originalPosition
     * @protected
     * @type Array
     */
    _originalPosition: null,

    /**
     * The list of elements that have been aria hidden when displaying
     * this dialogue.
     *
     * @property _hiddenSiblings
     * @protected
     * @type Array
     */
    _hiddenSiblings: null,

    /**
     * Hide the modal only if it doesn't contain a form.
     *
     * @method hideIfNotForm
     */
    hideIfNotForm: function() {
        var bb = this.get('boundingBox'),
            formElement = bb.one(FORM_SELECTOR);

        if (formElement === null) {
            this.hide();
        }
    },

    /**
     * Initialise the dialogue.
     *
     * @method initializer
     */
    initializer: function() {
        var bb;

        if (this.get('closeButton') !== false) {
            // The buttons constructor does not allow custom attributes
            this.get('buttons').header[0].setAttribute('title', this.get('closeButtonTitle'));
        }

        // Initialise the element cache.
        this._hiddenSiblings = [];

        if (this.get('render')) {
            this.render();
        }
        this.after('visibleChange', this.visibilityChanged, this);
        if (this.get('center')) {
            this.centerDialogue();
        }

        if (this.get('modal')) {
            // If we're a modal then make sure our container is ARIA
            // hidden by default. ARIA visibility is managed for modal dialogues.
            this.get(BASE).set('aria-hidden', 'true');
            this.plug(Y.M.core.LockScroll);
        }

        // Workaround upstream YUI bug http://yuilibrary.com/projects/yui3/ticket/2532507
        // and allow setting of z-index in theme.
        bb = this.get('boundingBox');
        bb.addClass(HAS_ZINDEX);

        // Add any additional classes that were specified.
        Y.Array.each(this.get('extraClasses'), bb.addClass, bb);

        if (this.get('visible')) {
            this.applyZIndex();
        }
        // Recalculate the zIndex every time the modal is altered.
        this.on('maskShow', this.applyZIndex);

        this.on('maskShow', function() {
            // When the mask shows, position the boundingBox at the top-left of the window such that when it is
            // focused, the position does not change.
            var w = Y.one(Y.config.win),
                bb = this.get('boundingBox');

            if (!this.get('center')) {
                this._originalPosition = bb.getXY();
            }

            // Check if maskNode already init click event.
            var maskNode = this.get('maskNode');
            if (this._currentMaskNodeId !== maskNode.get('_yuid')) {
                this._currentMaskNodeId = maskNode.get('_yuid');
                maskNode.on('click', this.hideIfNotForm, this);
            }

            if (bb.getStyle('position') !== 'fixed') {
                // If the boundingBox has been positioned in a fixed manner, then it will not position correctly to scrollTop.
                bb.setStyles({
                    top: w.get('scrollTop'),
                    left: w.get('scrollLeft')
                });
            }
        }, this);

        // Add any additional classes to the content node if required.
        var nBase = this.get('notificationBase');
        var additionalClasses = this.get('additionalBaseClass');
        if (additionalClasses !== '') {
            nBase.addClass(additionalClasses);
        }

        // Remove the dialogue from the DOM when it is destroyed.
        this.after('destroyedChange', function() {
            this.get(BASE).remove(true);
        }, this);
    },

    /**
     * Either set the zindex to the supplied value, or set it to one more than the highest existing
     * dialog in the page.
     *
     * @method applyZIndex
     */
    applyZIndex: function() {
        var highestzindex = 1,
            zindexvalue = 1,
            bb = this.get('boundingBox'),
            ol = this.get('maskNode'),
            zindex = this.get('zIndex');
        if (zindex !== 0 && !this._calculatedzindex) {
            // The zindex was specified so we should use that.
            bb.setStyle('zIndex', zindex);
        } else {
            // Determine the correct zindex by looking at all existing dialogs and menubars in the page.
            Y.all(DIALOGUE_SELECTOR + ', ' + MENUBAR_SELECTOR + ', ' + DOT + HAS_ZINDEX).each(function(node) {
                var zindex = this.findZIndex(node);
                if (zindex > highestzindex) {
                    highestzindex = zindex;
                }
            }, this);
            // Only set the zindex if we found a wrapper.
            zindexvalue = (highestzindex + 1).toString();
            bb.setStyle('zIndex', zindexvalue);
            this.set('zIndex', zindexvalue);
            if (this.get('modal')) {
                ol.setStyle('zIndex', zindexvalue);

                // In IE8, the z-indexes do not take effect properly unless you toggle
                // the lightbox from 'fixed' to 'static' and back. This code does so
                // using the minimum setTimeouts that still actually work.
                if (Y.UA.ie && Y.UA.compareVersions(Y.UA.ie, 9) < 0) {
                    setTimeout(function() {
                        ol.setStyle('position', 'static');
                        setTimeout(function() {
                            ol.setStyle('position', 'fixed');
                        }, 0);
                    }, 0);
                }
            }
            this._calculatedzindex = true;
        }
    },

    /**
     * Finds the zIndex of the given node or its parent.
     *
     * @method findZIndex
     * @param {Node} node The Node to apply the zIndex to.
     * @return {Number} Either the zIndex, or 0 if one was not found.
     */
    findZIndex: function(node) {
        // In most cases the zindex is set on the parent of the dialog.
        var zindex = node.getStyle('zIndex') || node.ancestor().getStyle('zIndex');
        if (zindex) {
            return parseInt(zindex, 10);
        }
        return 0;
    },

    /**
     * Event listener for the visibility changed event.
     *
     * @method visibilityChanged
     * @param {EventFacade} e
     */
    visibilityChanged: function(e) {
        var titlebar, bb;
        if (e.attrName === 'visible') {
            this.get('maskNode').addClass(CSS.LIGHTBOX);
            // Going from visible to hidden.
            if (e.prevVal && !e.newVal) {
                bb = this.get('boundingBox');
                if (this._resizeevent) {
                    this._resizeevent.detach();
                    this._resizeevent = null;
                }
                if (this._orientationevent) {
                    this._orientationevent.detach();
                    this._orientationevent = null;
                }
                bb.detach('key', this.keyDelegation);

                if (this.get('modal')) {
                    // Hide this dialogue from screen readers.
                    this.setAccessibilityHidden();
                }
            }
            // Going from hidden to visible.
            if (!e.prevVal && e.newVal) {
                // This needs to be done each time the dialog is shown as new dialogs may have been opened.
                this.applyZIndex();
                // This needs to be done each time the dialog is shown as the window may have been resized.
                this.makeResponsive();
                if (!this.shouldResizeFullscreen()) {
                    if (this.get('draggable')) {
                        titlebar = '#' + this.get('id') + ' .' + CSS.HEADER;
                        this.plug(Y.Plugin.Drag, {handles: [titlebar]});
                        Y.one(titlebar).setStyle('cursor', 'move');
                    }
                }
                this.keyDelegation();

                // Only do accessibility hiding for modals because the ARIA spec
                // says that all ARIA dialogues should be modal.
                if (this.get('modal')) {
                    // Make this dialogue visible to screen readers.
                    this.setAccessibilityVisible();
                }
            }
            if (this.get('center') && !e.prevVal && e.newVal) {
                this.centerDialogue();
            }
        }
    },
    /**
     * If the responsive attribute is set on the dialog, and the window size is
     * smaller than the responsive width - make the dialog fullscreen.
     *
     * @method makeResponsive
     */
    makeResponsive: function() {
        var bb = this.get('boundingBox');

        if (this.shouldResizeFullscreen()) {
            // Make this dialogue fullscreen on a small screen.
            // Disable the page scrollbars.

            // Size and position the fullscreen dialog.

            bb.addClass(DIALOGUE_FULLSCREEN_CLASS);
            bb.setStyles({'left': null,
                          'top': null,
                          'width': null,
                          'height': null,
                          'right': null,
                          'bottom': null});
        } else {
            if (this.get('responsive')) {
                // We must reset any of the fullscreen changes.
                bb.removeClass(DIALOGUE_FULLSCREEN_CLASS)
                    .setStyles({'width': this.get('width'),
                                'height': this.get('height')});
            }
        }

        // Update Lock scroll if the plugin is present.
        if (this.lockScroll) {
            this.lockScroll.updateScrollLock(this.shouldResizeFullscreen());
        }
    },
    /**
     * Center the dialog on the screen.
     *
     * @method centerDialogue
     */
    centerDialogue: function() {
        var bb = this.get('boundingBox'),
            hidden = bb.hasClass(DIALOGUE_HIDDEN_CLASS),
            x,
            y;

        // Don't adjust the position if we are in full screen mode.
        if (this.shouldResizeFullscreen()) {
            return;
        }
        if (hidden) {
            bb.setStyle('top', '-1000px').removeClass(DIALOGUE_HIDDEN_CLASS);
        }
        x = Math.max(Math.round((bb.get('winWidth') - bb.get('offsetWidth')) / 2), 15);
        y = Math.max(Math.round((bb.get('winHeight') - bb.get('offsetHeight')) / 2), 15) + Y.one(window).get('scrollTop');
        bb.setStyles({'left': x, 'top': y});

        if (hidden) {
            bb.addClass(DIALOGUE_HIDDEN_CLASS);
        }
        this.makeResponsive();
    },
    /**
     * Return whether this dialogue should be fullscreen or not.
     *
     * Responsive attribute must be true and we should not be in an iframe and the screen width should
     * be less than the responsive width.
     *
     * @method shouldResizeFullscreen
     * @return {Boolean}
     */
    shouldResizeFullscreen: function() {
        return (window === window.parent) && this.get('responsive') &&
               Math.floor(Y.one(document.body).get('winWidth')) < this.get('responsiveWidth');
    },

    show: function() {
        var result = null,
            header = this.headerNode,
            content = this.bodyNode,
            focusSelector = this.get('focusOnShowSelector'),
            focusNode = null;

        result = DIALOGUE.superclass.show.call(this);

        if (!this.get('center') && this._originalPosition) {
            // Restore the dialogue position to it's location before it was moved at show time.
            this.get('boundingBox').setXY(this._originalPosition);
        }

        // Try and find a node to focus on using the focusOnShowSelector attribute.
        if (focusSelector !== null) {
            focusNode = this.get('boundingBox').one(focusSelector);
        }
        if (!focusNode) {
            // Fall back to the header or the content if no focus node was found yet.
            if (header && header !== '') {
                focusNode = header;
            } else if (content && content !== '') {
                focusNode = content;
            }
        }
        if (focusNode) {
            focusNode.focus();
        }
        return result;
    },

    hide: function(e) {
        if (e) {
            // If the event was closed by an escape key event, then we need to check that this
            // dialogue is currently focused to prevent closing all dialogues in the stack.
            if (e.type === 'key' && e.keyCode === 27 && !this.get('focused')) {
                return;
            }
        }

        // Unlock scroll if the plugin is present.
        if (this.lockScroll) {
            this.lockScroll.disableScrollLock();
        }

        return DIALOGUE.superclass.hide.call(this, arguments);
    },
    /**
     * Setup key delegation to keep tabbing within the open dialogue.
     *
     * @method keyDelegation
     */
    keyDelegation: function() {
        var bb = this.get('boundingBox');
        bb.delegate('key', function(e) {
            var target = e.target;
            var direction = 'forward';
            if (e.shiftKey) {
                direction = 'backward';
            }
            if (this.trapFocus(target, direction)) {
                e.preventDefault();
            }
        }, 'down:9', CAN_RECEIVE_FOCUS_SELECTOR, this);
    },

    /**
     * Trap the tab focus within the open modal.
     *
     * @method trapFocus
     * @param {string} target the element target
     * @param {string} direction tab key for forward and tab+shift for backward
     * @return {Boolean} The result of the focus action.
     */
    trapFocus: function(target, direction) {
        var bb = this.get('boundingBox'),
            firstitem = bb.one(CAN_RECEIVE_FOCUS_SELECTOR),
            lastitem = bb.all(CAN_RECEIVE_FOCUS_SELECTOR).pop();

        if (target === lastitem && direction === 'forward') { // Tab key.
            return firstitem.focus();
        } else if (target === firstitem && direction === 'backward') {  // Tab+shift key.
            return lastitem.focus();
        }
    },

    /**
     * Sets the appropriate aria attributes on this dialogue and the other
     * elements in the DOM to ensure that screen readers are able to navigate
     * the dialogue popup correctly.
     *
     * @method setAccessibilityVisible
     */
    setAccessibilityVisible: function() {
        // Get the element that contains this dialogue because we need it
        // to filter out from the document.body child elements.
        var container = this.get(BASE);

        // We need to get a list containing each sibling element and the shallowest
        // non-ancestral nodes in the DOM. We can shortcut this a little by leveraging
        // the fact that this dialogue is always appended to the document body therefore
        // it's siblings are the shallowest non-ancestral nodes. If that changes then
        // this code should also be updated.
        Y.one(document.body).get('children').each(function(node) {
            // Skip the element that contains us.
            if (node !== container) {
                var hidden = node.get('aria-hidden');
                // If they are already hidden we can ignore them.
                if (hidden !== 'true') {
                    // Save their current state.
                    node.setData('previous-aria-hidden', hidden);
                    this._hiddenSiblings.push(node);

                    // Hide this node from screen readers.
                    node.set('aria-hidden', 'true');
                }
            }
        }, this);

        // Make us visible to screen readers.
        container.set('aria-hidden', 'false');
    },

    /**
     * Restores the aria visibility on the DOM elements changed when displaying
     * the dialogue popup and makes the dialogue aria hidden to allow screen
     * readers to navigate the main page correctly when the dialogue is closed.
     *
     * @method setAccessibilityHidden
     */
    setAccessibilityHidden: function() {
        var container = this.get(BASE);
        container.set('aria-hidden', 'true');

        // Restore the sibling nodes back to their original values.
        Y.Array.each(this._hiddenSiblings, function(node) {
            var previousValue = node.getData('previous-aria-hidden');
            // If the element didn't previously have an aria-hidden attribute
            // then we can just remove the one we set.
            if (previousValue === null) {
                node.removeAttribute('aria-hidden');
            } else {
                // Otherwise set it back to the old value (which will be false).
                node.set('aria-hidden', previousValue);
            }
        });

        // Clear the cache. No longer need to store these.
        this._hiddenSiblings = [];
    }
}, {
    NAME: DIALOGUE_NAME,
    CSS_PREFIX: DIALOGUE_PREFIX,
    ATTRS: {
        /**
         * Any additional classes to add to the base Node.
         *
         * @attribute additionalBaseClass
         * @type String
         * @default ''
         */
        additionalBaseClass: {
            value: ''
        },

        /**
         * The Notification base Node.
         *
         * @attribute notificationBase
         * @type Node
         */
        notificationBase: {

        },

        /**
         * Whether to display the dialogue modally and with a
         * lightbox style.
         *
         * @attribute lightbox
         * @type Boolean
         * @default true
         * @deprecated Since Moodle 2.7. Please use modal instead.
         */
        lightbox: {
            lazyAdd: false,
            setter: function(value) {
                Y.log("The lightbox attribute of M.core.dialogue has been deprecated since Moodle 2.7, " +
                      "please use the modal attribute instead",
                    'warn', 'moodle-core-notification-dialogue');
                this.set('modal', value);
            }
        },

        /**
         * Whether to display a close button on the dialogue.
         *
         * Note, we do not recommend hiding the close button as this has
         * potential accessibility concerns.
         *
         * @attribute closeButton
         * @type Boolean
         * @default true
         */
        closeButton: {
            validator: Y.Lang.isBoolean,
            value: true
        },

        /**
         * The title for the close button if one is to be shown.
         *
         * @attribute closeButtonTitle
         * @type String
         * @default 'Close'
         */
        closeButtonTitle: {
            validator: Y.Lang.isString,
            value: M.util.get_string('closebuttontitle', 'moodle')
        },

        /**
         * Whether to display the dialogue centrally on the screen.
         *
         * @attribute center
         * @type Boolean
         * @default true
         */
        center: {
            validator: Y.Lang.isBoolean,
            value: true
        },

        /**
         * Whether to make the dialogue movable around the page.
         *
         * @attribute draggable
         * @type Boolean
         * @default false
         */
        draggable: {
            validator: Y.Lang.isBoolean,
            value: false
        },

        /**
         * Used to generate a unique id for the dialogue.
         *
         * @attribute COUNT
         * @type String
         * @default null
         * @writeonce
         */
        COUNT: {
            writeOnce: true,
            valueFn: function() {
                return Y.stamp(this);
            }
        },

        /**
         * Used to disable the fullscreen resizing behaviour if required.
         *
         * @attribute responsive
         * @type Boolean
         * @default true
         */
        responsive: {
            validator: Y.Lang.isBoolean,
            value: true
        },

        /**
         * The width that this dialogue should be resized to fullscreen.
         *
         * @attribute responsiveWidth
         * @type Number
         * @default 768
         */
        responsiveWidth: {
            value: 768
        },

        /**
         * Selector to a node that should recieve focus when this dialogue is shown.
         *
         * The default behaviour is to focus on the header.
         *
         * @attribute focusOnShowSelector
         * @default null
         * @type String
         */
        focusOnShowSelector: {
            value: null
        }
    }
});

Y.Base.modifyAttrs(DIALOGUE, {
    /**
     * String with units, or number, representing the width of the Widget.
     * If a number is provided, the default unit, defined by the Widgets
     * DEF_UNIT, property is used.
     *
     * If a value of 'auto' is used, then an empty String is instead
     * returned.
     *
     * @attribute width
     * @default '400px'
     * @type {String|Number}
     */
    width: {
        value: '400px',
        setter: function(value) {
            if (value === 'auto') {
                return '';
            }
            return value;
        }
    },

    /**
     * Boolean indicating whether or not the Widget is visible.
     *
     * We override this from the default Widget attribute value.
     *
     * @attribute visible
     * @default false
     * @type Boolean
     */
    visible: {
        value: false
    },

    /**
     * A convenience Attribute, which can be used as a shortcut for the
     * `align` Attribute.
     *
     * Note: We override this in Moodle such that it sets a value for the
     * `center` attribute if set. The `centered` will always return false.
     *
     * @attribute centered
     * @type Boolean|Node
     * @default false
     */
    centered: {
        setter: function(value) {
            if (value) {
                this.set('center', true);
            }
            return false;
        }
    },

    /**
     * Boolean determining whether to render the widget during initialisation.
     *
     * We override this to change the default from false to true for the dialogue.
     * We then proceed to early render the dialogue during our initialisation rather than waiting
     * for YUI to render it after that.
     *
     * @attribute render
     * @type Boolean
     * @default true
     */
    render: {
        value: true,
        writeOnce: true
    },

    /**
     * Any additional classes to add to the boundingBox.
     *
     * @attribute extraClasses
     * @type Array
     * @default []
     */
    extraClasses: {
        value: []
    },

    /**
     * Identifier for the widget.
     *
     * @attribute id
     * @type String
     * @default a product of guid().
     * @writeOnce
     */
    id: {
        writeOnce: true,
        valueFn: function() {
            var id = 'moodle-dialogue-' + Y.stamp(this);
            return id;
        }
    },

    /**
     * Collection containing the widget's buttons.
     *
     * @attribute buttons
     * @type Object
     * @default {}
     */
    buttons: {
        getter: Y.WidgetButtons.prototype._getButtons,
        setter: Y.WidgetButtons.prototype._setButtons,
        valueFn: function() {
            if (this.get('closeButton') === false) {
                return null;
            } else {
                return [
                    {
                        section: Y.WidgetStdMod.HEADER,
                        classNames: 'closebutton',
                        action: function() {
                            this.hide();
                        }
                    }
                ];
            }
        }
    }
});

Y.Base.mix(DIALOGUE, [Y.M.core.WidgetFocusAfterHide]);

M.core.dialogue = DIALOGUE;
/* global DIALOGUE_PREFIX */

/**
 * A dialogue type designed to display informative messages to users.
 *
 * @module moodle-core-notification
 */

/**
 * Extends core Dialogue to provide a type of dialogue which can be used
 * for informative message which are modal, and centered.
 *
 * @param {Object} config Object literal specifying the dialogue configuration properties.
 * @constructor
 * @class M.core.notification.info
 * @extends M.core.dialogue
 */
var INFO = function() {
    INFO.superclass.constructor.apply(this, arguments);
};

Y.extend(INFO, M.core.dialogue, {
    initializer: function() {
        this.show();
    }
}, {
    NAME: 'Moodle information dialogue',
    CSS_PREFIX: DIALOGUE_PREFIX
});

Y.Base.modifyAttrs(INFO, {
   /**
    * Whether the widget should be modal or not.
    *
    * We override this to change the default from false to true for a subset of dialogues.
    *
    * @attribute modal
    * @type Boolean
    * @default true
    */
    modal: {
        validator: Y.Lang.isBoolean,
        value: true
    }
});

M.core.notification = M.core.notification || {};
M.core.notification.info = INFO;


}, '@VERSION@', {
    "requires": [
        "base",
        "node",
        "panel",
        "escape",
        "event-key",
        "dd-plugin",
        "moodle-core-widget-focusafterclose",
        "moodle-core-lockscroll"
    ]
});
YUI.add('moodle-core-tooltip', function (Y, NAME) {

/**
 * Provides the base tooltip class.
 *
 * @module moodle-core-tooltip
 */

/**
 * A base class for a tooltip.
 *
 * @param {Object} config Object literal specifying tooltip configuration properties.
 * @class M.core.tooltip
 * @constructor
 * @extends M.core.dialogue
 */
function TOOLTIP(config) {
    if (!config) {
        config = {};
    }

    // Override the default options provided by the parent class.
    if (typeof config.draggable === 'undefined') {
        config.draggable = true;
    }

    if (typeof config.constrain === 'undefined') {
        config.constrain = true;
    }

    TOOLTIP.superclass.constructor.apply(this, [config]);
}

var SELECTORS = {
        CLOSEBUTTON: '.closebutton'
    },

    CSS = {
        PANELTEXT: 'tooltiptext'
    },
    RESOURCES = {
        WAITICON: {
            pix: 'i/loading_small',
            component: 'moodle'
        }
    },
    ATTRS = {};

/**
 * Static property provides a string to identify the JavaScript class.
 *
 * @property NAME
 * @type String
 * @static
 */
TOOLTIP.NAME = 'moodle-core-tooltip';

/**
 * Static property used to define the CSS prefix applied to tooltip dialogues.
 *
 * @property CSS_PREFIX
 * @type String
 * @static
 */
TOOLTIP.CSS_PREFIX = 'moodle-dialogue';

/**
 * Static property used to define the default attribute configuration for the Tooltip.
 *
 * @property ATTRS
 * @type String
 * @static
 */
TOOLTIP.ATTRS = ATTRS;

/**
 * The initial value of the header region before the content finishes loading.
 *
 * @attribute initialheadertext
 * @type String
 * @default ''
 * @writeOnce
 */
ATTRS.initialheadertext = {
    value: ''
};

/**
  * The initial value of the body region before the content finishes loading.
  *
  * The supplid string will be wrapped in a div with the CSS.PANELTEXT class and a standard Moodle spinner
  * appended.
  *
  * @attribute initialbodytext
  * @type String
  * @default ''
  * @writeOnce
  */
ATTRS.initialbodytext = {
    value: '',
    setter: function(content) {
        var parentnode,
            spinner;
        parentnode = Y.Node.create('<div />')
            .addClass(CSS.PANELTEXT);

        spinner = Y.Node.create('<img />')
            .setAttribute('src', M.util.image_url(RESOURCES.WAITICON.pix, RESOURCES.WAITICON.component))
            .addClass('spinner');

        if (content) {
            // If we have been provided with content, add it to the parent and make
            // the spinner appear correctly inline
            parentnode.set('text', content);
            spinner.addClass('iconsmall');
        } else {
            // If there is no loading message, just make the parent node a lightbox
            parentnode.addClass('content-lightbox');
        }

        parentnode.append(spinner);
        return parentnode;
    }
};

/**
 * The initial value of the footer region before the content finishes loading.
 *
 * If a value is supplied, it will be wrapped in a <div> first.
 *
 * @attribute initialfootertext
 * @type String
 * @default ''
 * @writeOnce
 */
ATTRS.initialfootertext = {
    value: null,
    setter: function(content) {
        if (content) {
            return Y.Node.create('<div />')
                .set('text', content);
        }
    }
};

/**
 * The function which handles setting the content of the title region.
 * The specified function will be called with a context of the tooltip instance.
 *
 * The default function will simply set the value of the title to object.heading as returned by the AJAX call.
 *
 * @attribute headerhandler
 * @type Function|String|null
 * @default set_header_content
 */
ATTRS.headerhandler = {
    value: 'set_header_content'
};

/**
 * The function which handles setting the content of the body region.
 * The specified function will be called with a context of the tooltip instance.
 *
 * The default function will simply set the value of the body area to a div containing object.text as returned
 * by the AJAX call.
 *
 * @attribute bodyhandler
 * @type Function|String|null
 * @default set_body_content
 */
ATTRS.bodyhandler = {
    value: 'set_body_content'
};

/**
 * The function which handles setting the content of the footer region.
 * The specified function will be called with a context of the tooltip instance.
 *
 * By default, the footer is not set.
 *
 * @attribute footerhandler
 * @type Function|String|null
 * @default null
 */
ATTRS.footerhandler = {
    value: null
};

/**
 * The function which handles modifying the URL that was clicked on.
 *
 * The default function rewrites '.php' to '_ajax.php'.
 *
 * @attribute urlmodifier
 * @type Function|String|null
 * @default null
 */
ATTRS.urlmodifier = {
    value: null
};

/**
 * Set the Y.Cache object to use.
 *
 * By default a new Y.Cache object will be created for each instance of the tooltip.
 *
 * In certain situations, where multiple tooltips may share the same cache, it may be preferable to
 * seed this cache from the calling method.
 *
 * @attribute textcache
 * @type Y.Cache|null
 * @default null
 */
ATTRS.textcache = {
    value: null
};

/**
 * Set the default size of the Y.Cache object.
 *
 * This is only used if no textcache is specified.
 *
 * @attribute textcachesize
 * @type Number
 * @default 10
 */
ATTRS.textcachesize = {
    value: 10
};

Y.extend(TOOLTIP, M.core.dialogue, {
    // The bounding box.
    bb: null,

    // Any event listeners we may need to cancel later.
    listenevents: [],

    // Cache of objects we've already retrieved.
    textcache: null,

    // The align position. This differs for RTL languages so we calculate once and store.
    alignpoints: [
        Y.WidgetPositionAlign.TL,
        Y.WidgetPositionAlign.RC
    ],

    initializer: function() {
        // Set the initial values for the handlers.
        // These cannot be set in the attributes section as context isn't present at that time.
        if (!this.get('headerhandler')) {
            this.set('headerhandler', this.set_header_content);
        }
        if (!this.get('bodyhandler')) {
            this.set('bodyhandler', this.set_body_content);
        }
        if (!this.get('footerhandler')) {
            this.set('footerhandler', function() {});
        }
        if (!this.get('urlmodifier')) {
            this.set('urlmodifier', this.modify_url);
        }

        // Set up the dialogue with initial content.
        this.setAttrs({
            headerContent: this.get('initialheadertext'),
            bodyContent: this.get('initialbodytext'),
            footerContent: this.get('initialfootertext')
        });

        // Hide and then render the dialogue.
        this.hide();
        this.render();

        // Hook into a few useful areas.
        this.bb = this.get('boundingBox');

        // Add an additional class to the boundingbox to allow tooltip-specific style to be
        // set.
        this.bb.addClass('moodle-dialogue-tooltip');

        // Change the alignment if this is an RTL language.
        if (window.right_to_left()) {
            this.alignpoints = [
                Y.WidgetPositionAlign.TR,
                Y.WidgetPositionAlign.LC
            ];
        }

        // Set up the text cache if it's not set up already.
        if (!this.get('textcache')) {
            this.set('textcache', new Y.Cache({
                // Set a reasonable maximum cache size to prevent memory growth.
                max: this.get('textcachesize')
            }));
        }

        // Disable the textcache when in developerdebug.
        if (M.cfg.developerdebug) {
            this.get('textcache').set('max', 0);
        }

        return this;
    },

    /**
     * Display the tooltip for the clicked link.
     *
     * The anchor for the clicked link is used.
     *
     * @method display_panel
     * @param {EventFacade} e The event from the clicked link. This is used to determine the clicked URL.
     */
    display_panel: function(e) {
        var clickedlink, thisevent, ajaxurl, config, cacheentry;

        // Prevent the default click action and prevent the event triggering anything else.
        e.preventDefault();

        // Cancel any existing listeners and close the panel if it's already open.
        this.cancel_events();

        // Grab the clickedlink - this contains the URL we fetch and we align the panel to it.
        clickedlink = e.target.ancestor('a', true);

        // Reset the initial text to a spinner while we retrieve the text.
        this.setAttrs({
            headerContent: this.get('initialheadertext'),
            bodyContent: this.get('initialbodytext'),
            footerContent: this.get('initialfootertext')
        });

        // Now that initial setup has begun, show the panel.
        this.show(e);

        // Align with the link that was clicked.
        this.align(clickedlink, this.alignpoints);

        // Add some listen events to close on.
        thisevent = this.bb.delegate('click', this.close_panel, SELECTORS.CLOSEBUTTON, this);
        this.listenevents.push(thisevent);

        thisevent = Y.one('body').on('key', this.close_panel, 'esc', this);
        this.listenevents.push(thisevent);

        // Listen for mousedownoutside events - clickoutside is broken on IE.
        thisevent = this.bb.on('mousedownoutside', this.close_panel, this);
        this.listenevents.push(thisevent);

        // Modify the URL as required.
        ajaxurl = Y.bind(this.get('urlmodifier'), this, clickedlink.get('href'))();

        cacheentry = this.get('textcache').retrieve(ajaxurl);
        if (cacheentry) {
            // The data from this help call was already cached so use that and avoid an AJAX call.
            this._set_panel_contents(cacheentry.response);
        } else {
            // Retrieve the actual help text we should use.
            config = {
                method: 'get',
                context: this,
                sync: false,
                on: {
                    complete: function(tid, response) {
                        this._set_panel_contents(response.responseText, ajaxurl);
                    }
                }
            };

            Y.io(ajaxurl, config);
        }
    },

    _set_panel_contents: function(response, ajaxurl) {
        var responseobject;

        // Attempt to parse the response into an object.
        try {
            responseobject = Y.JSON.parse(response);
            if (responseobject.error) {
                this.close_panel();
                Y.use('moodle-core-notification-ajaxexception', function() {
                    return new M.core.ajaxException(responseobject).show();
                });
                return this;
            }
        } catch (error) {
            this.close_panel();
            Y.use('moodle-core-notification-exception', function() {
                return new M.core.exception(error).show();
            });
            return this;
        }

        // Set the contents using various handlers.
        // We must use Y.bind to ensure that the correct context is used when the default handlers are overridden.
        Y.bind(this.get('headerhandler'), this, responseobject)();
        Y.bind(this.get('bodyhandler'), this, responseobject)();
        Y.bind(this.get('footerhandler'), this, responseobject)();

        if (ajaxurl) {
            // Ensure that this data is added to the cache.
            this.get('textcache').add(ajaxurl, response);
        }

        this.get('buttons').header[0].focus();
    },

    set_header_content: function(responseobject) {
        this.set('headerContent', responseobject.heading);
    },

    set_body_content: function(responseobject) {
        var bodycontent = Y.Node.create('<div />')
            .set('innerHTML', responseobject.text)
            .setAttribute('role', 'alert')
            .addClass(CSS.PANELTEXT);
        this.set('bodyContent', bodycontent);
    },

    modify_url: function(url) {
        return url.replace(/\.php\?/, '_ajax.php?');
    },

    close_panel: function(e) {
        // Hide the panel first.
        this.hide(e);

        // Cancel the listeners that we added in display_panel.
        this.cancel_events();

        // Prevent any default click that the close button may have.
        if (e) {
            e.preventDefault();
        }
    },

    cancel_events: function() {
        // Detach all listen events to prevent duplicate triggers.
        var thisevent;
        while (this.listenevents.length) {
            thisevent = this.listenevents.shift();
            thisevent.detach();
        }
    }
});

Y.Base.modifyAttrs(TOOLTIP, {
    /**
     * Whether the widget should be modal or not.
     *
     * Moodle override: We override this for tooltip to default it to false.
     *
     * @attribute Modal
     * @type Boolean
     * @default false
     */
    modal: {
        value: false
    },

    focusOnPreviousTargetAfterHide: {
        value: true
    }
});

M.core = M.core || {};
M.core.tooltip = M.core.tooltip = TOOLTIP;


}, '@VERSION@', {
    "requires": [
        "base",
        "node",
        "io-base",
        "moodle-core-notification-dialogue",
        "json-parse",
        "widget-position",
        "widget-position-align",
        "event-outside",
        "cache-base"
    ]
});
YUI.add('moodle-core-popuphelp', function (Y, NAME) {

/**
 * A popup help dialogue for Moodle.
 *
 * @module moodle-core-popuphelp
 */

/**
 * A popup help dialogue for Moodle.
 *
 * @class M.core.popuphelp
 * @constructor
 */

function POPUPHELP() {
    POPUPHELP.superclass.constructor.apply(this, arguments);
}

var SELECTORS = {
        CLICKABLELINKS: 'span.helptooltip > a',
        FOOTER: 'div.moodle-dialogue-ft'
    },

    CSS = {
        ICON: 'icon',
        ICONPRE: 'icon-pre'
    },
    ATTRS = {};

// Set the modules base properties.
POPUPHELP.NAME = 'moodle-core-popuphelp';
POPUPHELP.ATTRS = ATTRS;

Y.extend(POPUPHELP, Y.Base, {
    panel: null,

    /**
     * Setup the popuphelp.
     *
     * @method initializer
     */
    initializer: function() {
        Y.one('body').delegate('click', this.display_panel, SELECTORS.CLICKABLELINKS, this);
    },

    /**
     * Display the help tooltip.
     *
     * @method display_panel
     * @param {EventFacade} e
     */
    display_panel: function(e) {
        if (!this.panel) {
            this.panel = new M.core.tooltip({
                bodyhandler: this.set_body_content,
                footerhandler: this.set_footer,
                initialheadertext: M.util.get_string('loadinghelp', 'moodle'),
                initialfootertext: ''
            });
        }

        // Call the tooltip setup.
        this.panel.display_panel(e);
    },

    /**
     * Override the footer handler to add a 'More help' link where relevant.
     *
     * @method set_footer
     * @param {Object} helpobject The object returned from the AJAX call.
     */
    set_footer: function(helpobject) {
        // Check for an optional link to documentation on moodle.org.
        if (helpobject.doclink) {
            // Wrap a help icon and the morehelp text in an anchor. The class of the anchor should
            // determine whether it's opened in a new window or not.
            var doclink = Y.Node.create('<a />')
                .setAttrs({
                    'href': helpobject.doclink.link
                })
                .addClass(helpobject.doclink['class']);
            var helpicon = Y.Node.create('<img />')
                .setAttrs({
                    'src': M.util.image_url('docs', 'core')
                })
                .addClass(CSS.ICON)
                .addClass(CSS.ICONPRE);
            doclink.appendChild(helpicon);
            doclink.appendChild(helpobject.doclink.linktext);

            // Set the footerContent to the contents of the doclink.
            this.set('footerContent', doclink);
            this.bb.one(SELECTORS.FOOTER).show();
        } else {
            this.bb.one(SELECTORS.FOOTER).hide();
        }
    }
});
M.core = M.core || {};
M.core.popuphelp = M.core.popuphelp || null;
M.core.init_popuphelp = M.core.init_popuphelp || function(config) {
    // Only set up a single instance of the popuphelp.
    if (!M.core.popuphelp) {
        M.core.popuphelp = new POPUPHELP(config);
    }
    return M.core.popuphelp;
};


}, '@VERSION@', {"requires": ["moodle-core-tooltip"]});
