<?php

namespace Modules\Mnzguruapi;

use Zabbix\Core\CModule,
    APP,
    CMenu,
    CMenuItem;

class Module extends CModule {
    public function init(): void {
        APP::Component()->get('menu.main')
            ->findOrAdd(_('Administration'))
            ->getSubmenu()
            ->insertAfter(_('General'),
                (new CMenuItem(_('MonzGuru API Tool')))
                    ->setAction('mnzguruapi.view')
                    ->setIcon('icon-api')
            );
    }
} 