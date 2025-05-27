<?php

$page_title = _('MonzGuru - API Tool');

// Data for JavaScript
$zabbix_version = substr(ZABBIX_VERSION, 0, 3);

// API methods by version (based on original js.js)
$api_methods = [
    "6.0" => [
        "action.create", "action.delete", "action.get", "action.update",
        "alert.get", "apiinfo.version", "auditlog.get",
        "authentication.get", "authentication.update",
        "autoregistration.get", "autoregistration.update",
        "configuration.export", "configuration.import", "configuration.importcompare",
        "correlation.create", "correlation.delete", "correlation.get", "correlation.update",
        "dashboard.create", "dashboard.delete", "dashboard.get", "dashboard.update",
        "dhost.get", "dservice.get", "dcheck.get",
        "drule.create", "drule.delete", "drule.get", "drule.update",
        "event.acknowledge", "event.get",
        "graph.create", "graph.delete", "graph.get", "graph.update",
        "graphitem.get", "graphprototype.create", "graphprototype.delete",
        "graphprototype.get", "graphprototype.update",
        "hanode.get", "history.clear", "history.get",
        "host.create", "host.delete", "host.get", "host.massadd",
        "host.massremove", "host.massupdate", "host.update",
        "hostgroup.create", "hostgroup.delete", "hostgroup.get",
        "hostgroup.massadd", "hostgroup.massremove", "hostgroup.massupdate", "hostgroup.update",
        "hostinterface.create", "hostinterface.delete", "hostinterface.get",
        "hostinterface.massadd", "hostinterface.massremove", "hostinterface.replacehostinterfaces", "hostinterface.update",
        "hostprototype.create", "hostprototype.delete", "hostprototype.get", "hostprototype.update",
        "housekeeping.get", "housekeeping.update",
        "iconmap.create", "iconmap.delete", "iconmap.get", "iconmap.update",
        "image.create", "image.delete", "image.get", "image.update",
        "item.create", "item.delete", "item.get", "item.update",
        "itemprototype.create", "itemprototype.delete", "itemprototype.get", "itemprototype.update",
        "discoveryrule.copy", "discoveryrule.create", "discoveryrule.delete", "discoveryrule.get", "discoveryrule.update",
        "maintenance.create", "maintenance.delete", "maintenance.get", "maintenance.update",
        "map.create", "map.delete", "map.get", "map.update",
        "mediatype.create", "mediatype.delete", "mediatype.get", "mediatype.update",
        "problem.get", "proxy.create", "proxy.delete", "proxy.get", "proxy.update",
        "regexp.create", "regexp.delete", "regexp.get", "regexp.update",
        "report.create", "report.delete", "report.get", "report.update",
        "role.create", "role.delete", "role.get", "role.update",
        "script.create", "script.delete", "script.execute", "script.get", "script.getscriptsbyhosts", "script.update",
        "service.create", "service.delete", "service.get", "service.update",
        "settings.get", "settings.update",
        "sla.create", "sla.delete", "sla.get", "sla.getsli", "sla.update",
        "task.create", "task.get",
        "template.create", "template.delete", "template.get", "template.massadd",
        "template.massremove", "template.massupdate", "template.update",
        "templatedashboard.create", "templatedashboard.delete", "templatedashboard.get", "templatedashboard.update",
        "token.create", "token.delete", "token.generate", "token.get", "token.update",
        "trend.get",
        "trigger.adddependencies", "trigger.create", "trigger.delete", "trigger.deletedependencies",
        "trigger.get", "trigger.update",
        "triggerprototype.create", "triggerprototype.delete", "triggerprototype.get", "triggerprototype.update",
        "user.checkAuthentication", "user.create", "user.delete", "user.get",
        "user.login", "user.logout", "user.unblock", "user.update",
        "usergroup.create", "usergroup.delete", "usergroup.get", "usergroup.update",
        "usermacro.create", "usermacro.createglobal", "usermacro.delete", "usermacro.deleteglobal",
        "usermacro.get", "usermacro.update", "usermacro.updateglobal",
        "valuemap.create", "valuemap.delete", "valuemap.get", "valuemap.update",
        "httptest.create", "httptest.delete", "httptest.get", "httptest.update"
    ]
];

// Initialize data for JavaScript
insert_js('
    var zabbixVersion = "' . $zabbix_version . '";
    var methods = ' . json_encode($api_methods) . ';
    var config, ace;
');

// Create main page
(new CHtmlPage())
    ->setTitle($page_title)
    ->addItem([
        // Navigation bar
        (new CDiv())
            ->addClass('mnzguruapi-navbar')
            ->addItem([
                (new CDiv())
                    ->addClass('mnzguruapi-navbar-inner')
                    ->addItem([
                        (new CDiv())
                            ->addClass('mnzguruapi-container-fluid')
                            ->addItem([
                                (new CDiv())
                                    ->addClass('mnzguruapi-navbar-left')
                                    ->addItem([
                                        (new CSpan())
                                            ->addClass('mnzguruapi-brand')
                                            ->addItem((new CLink('MonzGuru - API Tool', '#'))),
                                        (new CList())
                                            ->addClass('mnzguruapi-nav')
                                            ->addItem([
                                                (new CListItem((new CLink(_('Zabbix API manual'), 'https://www.zabbix.com/documentation/current/manual/api'))
                                                    ->setAttribute('target', '_blank'))),
                                                (new CListItem((new CLink(_('GitHub'), 'https://github.com/Monzphere/monzguruapi'))
                                                    ->setAttribute('target', '_blank')))
                                            ])
                                    ]),
                                (new CSpan())
                                    ->addClass('mnzguruapi-navbar-text mnzguruapi-pull-right')
                                    ->setAttribute('id', 'connInfo')
                                    ->addItem(_('Connected via Internal API'))
                            ])
                    ])
            ]),

        // Main container
        (new CDiv())
            ->addClass('mnzguruapi-container-fluid')
            ->addItem([
                (new CDiv())
                    ->addClass('mnzguruapi-row-fluid')
                    ->addItem([
                        // Main column
                        (new CDiv())
                            ->addClass('mnzguruapi-span12')
                            ->addItem([
                                // Main form
                                (new CForm())
                                    ->setAttribute('id', 'mnzguruapi-form')
                                    ->addItem([
                                        (new CDiv())
                                            ->addClass('mnzguruapi-row-fluid')
                                            ->addItem([
                                                // Method field
                                                (new CDiv())
                                                    ->addClass('mnzguruapi-span6')
                                                    ->addItem([
                                                        (new CLabel(_('API Method'), 'apimethod'))
                                                            ->addClass('mnzguruapi-required'),
                                                        (new CTextBox('apimethod'))
                                                            ->setAttribute('id', 'apimethod')
                                                            ->setAttribute('placeholder', _('Type API method name'))
                                                            ->addClass('mnzguruapi-input-block-level')
                                                    ]),
                                                // Buttons
                                                (new CDiv())
                                                    ->addClass('mnzguruapi-span6')
                                                    ->addItem([
                                                        (new CButton('execute', _('Execute')))
                                                            ->setAttribute('id', 'execute')
                                                            ->addClass('mnzguruapi-btn mnzguruapi-btn-primary'),
                                                        (new CButton('save', _('Save')))
                                                            ->setAttribute('id', 'saveRequest')
                                                            ->addClass('mnzguruapi-btn'),
                                                        (new CButton('load', _('Load')))
                                                            ->setAttribute('id', 'loadRequest')
                                                            ->addClass('mnzguruapi-btn'),
                                                        (new CButton('example', _('Example')))
                                                            ->setAttribute('id', 'loadExample')
                                                            ->addClass('mnzguruapi-btn')
                                                    ])
                                            ]),
                                        // Parameters area
                                        (new CDiv())
                                            ->addClass('mnzguruapi-row-fluid')
                                            ->addItem([
                                                (new CDiv())
                                                    ->addClass('mnzguruapi-span12')
                                                    ->addItem([
                                                        (new CLabel(_('Parameters (JSON)'), 'apiparams')),
                                                        (new CTextArea('apiparams', '{}'))
                                                            ->setAttribute('id', 'apiparams')
                                                            ->setAttribute('rows', '8')
                                                            ->addClass('mnzguruapi-input-block-level')
                                                            ->setAttribute('style', 'font-family: monospace; font-size: 12px;')
                                                    ])
                                            ]),
                                        // Response area
                                        (new CDiv())
                                            ->addClass('mnzguruapi-row-fluid')
                                            ->addItem([
                                                (new CDiv())
                                                    ->addClass('mnzguruapi-span6')
                                                    ->addItem([
                                                        (new CDiv())
                                                            ->setAttribute('id', 'wrequest')
                                                            ->addItem([
                                                                (new CTag('h4', true, _('Request'))),
                                                                (new CTag('pre'))
                                                                    ->setAttribute('id', 'request')
                                                                    ->addClass('mnzguruapi-prettyprint')
                                                            ])
                                                    ]),
                                                (new CDiv())
                                                    ->addClass('mnzguruapi-span6')
                                                    ->addItem([
                                                        (new CDiv())
                                                            ->setAttribute('id', 'rsp')
                                                            ->addItem([
                                                                (new CTag('h4', true, _('Response'))),
                                                                (new CTag('pre'))
                                                                    ->setAttribute('id', 'response')
                                                                    ->addClass('mnzguruapi-prettyprint')
                                                            ])
                                                    ])
                                            ]),
                                        // JSONPath Debug area
                                        (new CDiv())
                                            ->addClass('mnzguruapi-row-fluid')
                                            ->addItem([
                                                (new CDiv())
                                                    ->addClass('mnzguruapi-span12')
                                                    ->addItem([
                                                        (new CLabel(_('JSONPath Debug'), 'jsonpath'))
                                                            ->setAttribute('title', _('Use JSONPath to extract specific data from response (ex: $.result[*].host)')),
                                                        (new CDiv())
                                                            ->addClass('mnzguruapi-jsonpath-container')
                                                            ->addItem([
                                                                (new CTextBox('jsonpath'))
                                                                    ->setAttribute('id', 'jsonpath')
                                                                    ->setAttribute('placeholder', _('Ex: result[*].host or result[0].hostid'))
                                                                    ->addClass('mnzguruapi-input-block-level mnzguruapi-jsonpath-input'),
                                                                (new CButton('test_jsonpath', _('Test JSONPath')))
                                                                    ->setAttribute('id', 'testJsonPath')
                                                                    ->addClass('mnzguruapi-btn mnzguruapi-btn-secondary')
                                                            ]),
                                                        (new CDiv())
                                                            ->setAttribute('id', 'jsonpath-result')
                                                            ->addClass('mnzguruapi-jsonpath-result hidden')
                                                            ->addItem([
                                                                (new CTag('h4', true, _('JSONPath Result'))),
                                                                (new CTag('pre'))
                                                                    ->setAttribute('id', 'jsonpath-output')
                                                                    ->addClass('mnzguruapi-prettyprint')
                                                            ])
                                                    ])
                                            ])
                                    ])
                            ])
                    ])
            ]),


    ])
    ->show();
?> 