/*! Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

;(function(Icinga) {

    var EventDB = function(module) {
        this.module = module;
        this.initialize();
    };

    EventDB.prototype = {
        initialize: function() {
            this.module.on('rendered', this.enableCopyable);

            var addCSSRule = function(sheet, selector, rules, index) {
                if('insertRule' in sheet) {
                    sheet.insertRule(selector + '{' + rules + '}', index);
                } else if('addRule' in sheet) {
                    sheet.addRule(selector, rules, index);
                } else {
                    this.module.icinga.logger.debug('Can\'t insert CSS rule');
                }
            };

            var sheet = (function() {
                var style = document.createElement('style');
                // WebKit hack
                style.appendChild(document.createTextNode(''));
                document.head.appendChild(style);
                return style.sheet;
            })();

            addCSSRule(
                sheet,
                '#layout.twocols.wide-layout #col1.module-eventdb, #layout.twocols.wide-layout #col1.module-eventdb ~ #col2',
                'width: 50%',
                0
            );
        },
        enableCopyable: function() {
            var e = this;
            $('.copyable').each(function() {
                var $button = $('<a>')
                    .attr('href', '#')
                    .addClass('action-link icon icon-globe copyable-button')
                    .text('Copy text');

                $button.on('click', function() {
                    var $el = $(this).parent().siblings('.copyable');
                    if ($el) {
                        e.selectText($el[0]);
                        document.execCommand('copy');
                        setTimeout(function () {
                            e.clearSelection()
                        }, 500);
                        if (icinga) {
                            icinga.loader.createNotice('info', 'Text copied to clipboard')
                        }
                    }
                });

                var $div = $('<div>').addClass('copyable-actions').append($button);

                $(this).before($div);
            });
        },
        selectText: function (text) {
            var doc = document, range, selection;
            if (doc.body.createTextRange) {
                range = document.body.createTextRange();
                range.moveToElementText(text);
                range.select();
            } else if (window.getSelection) {
                selection = window.getSelection();
                range = document.createRange();
                range.selectNodeContents(text);
                selection.removeAllRanges();
                selection.addRange(range);
            }
        },
        clearSelection: function() {
            if (document.selection) {
                document.selection.empty();
            } else if (window.getSelection) {
                window.getSelection().removeAllRanges();
            }
        }
    };

    Icinga.availableModules.eventdb = EventDB;
}(Icinga));
