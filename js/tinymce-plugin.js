/**
 * This file contains js functions for the teachpress tinyMCE plugin. This file is adapted from NextGen Gallery Plugin
 * 
 * @package teachpress
 * @subpackage js
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPLv2 or later
 */

// Self-executing function to create and register the TinyMCE plugin
(function(siteurl) {

    // Create the plugin. We'll register it afterwards
    tinymce.create('tinymce.plugins.teachpress', {

        /**
         * The WordPress Site URL
         */
        siteurl: siteurl,

        /**
         * Returns metadata about this plugin
         */
        getInfo: function() {
                return {
                    longname: 'teachPress',
                    author: 'Michael Winkler',
                    authorurl: 'mtrv.wordpress.com',
                    infourl: 'https://wordpress.org/plugins/teachpress/',
                    version: '0.1'
                };
        },

        /**
         * Initializes the plugin, this will be executed after the plugin has been created.
         */
        init: function(editor, plugin_url) {
            var self = this;

            // TinyMCE 4s events are a bit weird, but this lets us listen to the window-manager close event
            editor.windowManager.tpOldOpen = editor.windowManager.open;
            editor.windowManager.open = function(one, two) {
                var modal = editor.windowManager.tpOldOpen(one, two);
                modal.on('close', self.wm_close_event);
            };

            // Register a new TinyMCE command
            editor.addCommand('tp_attach_to_post', this.render_attach_to_post_interface, {
                editor: editor,
                plugin: editor.plugins.teachpress
            });

            // Add a button to trigger the above command
            editor.addButton('teachpress_attach_docs', {
                    title:	'teachpress_attach_docs.title',
                    cmd:	'tp_attach_to_post',
                    image:	plugin_url+'/logo_small_black.png'
            });

            // When the shortcode is clicked, open the attach to post interface
            editor.settings.extended_valid_elements += ",shortcode";
            editor.settings.custom_elements = "shortcode";

            editor.on('mouseup', function(e) {
                if (e.target.tagName == 'IMG') {
                    if (self.get_class_name(e.target).indexOf('tp_displayed_documents') >= 0) {
                        editor.dom.events.cancel(e);
                        var id = e.target.src.match(/\d+$/);
                        if (id) id = id.pop();
                        var obj = tinymce.extend(self, {
                            editor: editor,
                            plugin: editor.plugins.teachpress,
                            id:     id
                        });
                        self.render_attach_to_post_interface(id);
                    }
                }
                    });
            },

            get_class_name: function(node) {
                var class_name = node.getAttribute('class') ? node.getAttribute('class') : node.className;
                if (class_name) {
                    return class_name;
                } else {
                    return "";
                }
            },

            wm_close_event: function() {
                // Restore scrolling for the main content window when the attach to post interface is closed
                jQuery('html,body').css('overflow', 'auto');
                tinyMCE.activeEditor.selection.select(tinyMCE.activeEditor.dom.select('p')[0]);
                tinyMCE.activeEditor.selection.collapse(0);
            },

            /**
             * Renders the attach to post interface
             */
            render_attach_to_post_interface: function(id) {
                // Determine the attach to post url
                var attach_to_post_url = tp_attach_to_post_url;
                if (typeof(id) != 'undefined') {
                    attach_to_post_url += "?id=" + this.id;
                }

                var win = window;
                while (win.parent != null && win.parent != win) {
                        win = win.parent;
                }

                win = jQuery(win);
                var winWidth    = win.width();
                var winHeight   = win.height();
                var popupWidth  = 1200;
                var popupHeight = 600;
                var minWidth    = 800;
                var minHeight   = 600;
                var maxWidth    = winWidth  - (winWidth  * 0.05);
                var maxHeight   = winHeight - (winHeight * 0.05);

                if (maxWidth    < minWidth)  { maxWidth    = winWidth - 10;  }
                if (maxHeight   < minHeight) { maxHeight   = winHeight - 10; }
                if (popupWidth  > maxWidth)  { popupWidth  = maxWidth;  }
                if (popupHeight > maxHeight) { popupHeight = maxHeight; }

                // Open a window, occupying 90% of the screen real estate
                this.editor.windowManager.open({
                        url: attach_to_post_url,
                        id: 'tp_attach_to_post_dialog',
                        width: popupWidth,
                        height: popupHeight,
                        title: "teachPress - Attach To Post"
                });

                // Ensure that the window cannot be scrolled - XXX actually allow scrolling in the main window and disable it for the inner-windows/frames/elements as to create a single scrollbar
                jQuery('html,body').css('overflow', 'hidden');
                jQuery('#tp_attach_to_post_dialog').css('overflow-y', 'auto');
                jQuery('#tp_attach_to_post_dialog').css('overflow-x', 'hidden');
            }
    });

    // Register plugin
    tinymce.PluginManager.add('teachpress', tinymce.plugins.teachpress);
})(photocrati_ajax.wp_site_url);


