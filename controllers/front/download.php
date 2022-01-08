<?php

/**
 * PrestaShop Module - pitticatrovaprezzi
 *
 * Copyright 2020-2022 Pittica S.r.l.
 *
 * @category  Module
 * @package   Pittica/Trovaprezzi
 * @author    Lucio Benini <info@pittica.com>
 * @copyright 2020-2022 Pittica S.r.l.
 * @license   http://opensource.org/licenses/LGPL-3.0  The GNU Lesser General Public License, version 3.0 ( LGPL-3.0 )
 * @link      https://github.com/pittica/prestashop-trovaprezzi
 */

/**
 * Download controller.
 *
 * @category Controller
 * @package  Pittica/Trovaprezzi
 * @author   Lucio Benini <info@pittica.com>
 * @license  http://opensource.org/licenses/LGPL-3.0  The GNU Lesser General Public License, version 3.0 ( LGPL-3.0 )
 * @link     https://github.com/pittica/prestashop-trovaprezzi/blob/main/controllers/front/download.php
 * @since    1.0.0
 */
class pitticatrovaprezzidownloadModuleFrontController extends ModuleFrontController
{
    /**
     * {@inheritDoc}
     *
     * @return void
     * @since  1.0.0
     */
    public function initContent()
    {
        parent::initContent();

        if (Tools::getValue('token') === $this->module->getToken()) {
            $path = $this->module->getFilePath(Tools::getValue('provider', 'trovaprezzi'));

            if (file_exists($path)) {
                header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
                header('Cache-Control: public');
                header('Content-Type: text/xml');
                readfile($path);
            }
        }

        die();
    }
}
