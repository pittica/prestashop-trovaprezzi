<?php

/**
 * prestashop-trovaprezzi
 *
 * Copyright 2020 Pittica S.r.l.s.
 *
 * @author    Lucio Benini <info@pittica.com>
 * @copyright 2020 Pittica S.r.l.s.
 * @license   http://opensource.org/licenses/LGPL-3.0  The GNU Lesser General Public License, version 3.0 ( LGPL-3.0 )
 */

class pitticatrovaprezzidownloadModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        
        if (Tools::getValue('token') === $this->module->getToken()) {
            if (file_exists($this->module->getFilePath())) {
                header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
                header('Cache-Control: public');
                header('Content-Type: text/xml');
                readfile($this->module->getFilePath());
            }
        }
        
        die();
    }
}
