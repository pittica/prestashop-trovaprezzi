<?php

/**
 * PrestaShop Module - pitticatrovaprezzi
 *
 * Copyright 2020-2021 Pittica S.r.l.
 *
 * @category  Module
 * @package   Pittica/Trovaprezzi
 * @author    Lucio Benini <info@pittica.com>
 * @copyright 2020-2021 Pittica S.r.l.
 * @license   http://opensource.org/licenses/LGPL-3.0  The GNU Lesser General Public License, version 3.0 ( LGPL-3.0 )
 * @link      https://github.com/pittica/prestashop-trovaprezzi
 */

/**
 * Generator controller.
 *
 * @category Controller
 * @package  Pittica/Trovaprezzi
 * @author   Lucio Benini <info@pittica.com>
 * @license  http://opensource.org/licenses/LGPL-3.0  The GNU Lesser General Public License, version 3.0 ( LGPL-3.0 )
 * @link     https://github.com/pittica/prestashop-trovaprezzi/blob/main/controllers/front/download.php
 * @since    1.0.0
 */
class pitticatrovaprezzigenerateModuleFrontController extends ModuleFrontController
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
            $shop = (int) Tools::getValue('id_shop');

            $this->module->generate((int)Tools::getValue('refresh', true), Tools::getValue('provider'), $shop > 0 ? $shop : null);
        }

        die();
    }
}
