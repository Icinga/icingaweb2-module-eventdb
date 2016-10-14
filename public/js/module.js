/*! Icinga Web 2 | (c) 2016 Icinga Development Team | GPLv2+ */

;(function(Icinga) {

    var EventDB = function(module) {
        this.module = module;
        this.initialize();
    };

    EventDB.prototype = {
        initialize: function() {
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
        }
    };

    Icinga.availableModules.eventdb = EventDB;
}(Icinga));
