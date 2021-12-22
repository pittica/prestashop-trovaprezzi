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

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__) . '/../../classes/TrovaprezziOffer.php');

/**
 * Trovaprezzi admin controller.
 *
 * @category Controller
 * @package  Pittica/Trovaprezzi
 * @author   Lucio Benini <info@pittica.com>
 * @license  http://opensource.org/licenses/LGPL-3.0  The GNU Lesser General Public License, version 3.0 ( LGPL-3.0 )
 * @link     https://github.com/pittica/prestashop-trovaprezzi/blob/main/controllers/admin/AdminTrovaprezziController.php
 * @since    1.0.0
 */
class AdminTrovaprezziController extends ModuleAdminController
{
    /**
     * {@inheritDoc}
     *
     * @return void
     * @since  1.0.0
     */
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = TrovaprezziOffer::TABLE_NAME;
        $this->lang = false;
        $this->className = TrovaprezziOffer::class;
        $this->display = 'list';
        $this->actions = array('view');
        $this->identifier = 'id_pittica_trovaprezzi_offer';
        $this->actions_available = array('view');

        parent::__construct();

        $this->title = 'TrovaPrezzi';
        $this->meta_title = array('TrovaPrezzi');

        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
        }

        $this->fields_list = array(
            'id_product' => array(
                'title' => $this->trans('ID', array(), 'Admin.Global'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ),
            'name' => array(
                'title' => $this->l('Product'),
                'filter_key' => 'a!name',
            ),
            'active' => array(
                'title' => $this->trans('Enabled', array(), 'Admin.Global'),
                'active' => 'status',
                'filter_key' => 'a!active',
                'align' => 'center',
                'type' => 'bool',
                'orderby' => true,
                'class' => 'fixed-width-sm',
            ),
            'has_code' => array(
                'title' => $this->l('Code'),
                'align' => 'center',
                'type' => 'bool',
                'orderby' => true,
                'filter' => false,
                'search' => false,
                'class' => 'fixed-width-sm',
            ),
            'has_image' => array(
                'title' => $this->l('Image'),
                'align' => 'center',
                'type' => 'bool',
                'orderby' => true,
                'filter' => false,
                'search' => false,
                'class' => 'fixed-width-sm',
            ),
            'has_categories' => array(
                'title' => $this->l('Categories'),
                'align' => 'center',
                'type' => 'bool',
                'orderby' => true,
                'filter' => false,
                'search' => false,
                'class' => 'fixed-width-sm',
            )
        );
        
        $this->bulk_actions = array(
            'enableSelection' => array(
                'text' => $this->trans('Enable selection', array(), 'Admin.Actions'),
                'icon' => 'icon-power-off text-success',
            ),
            'disableSelection' => array(
                'text' => $this->trans('Disable selection', array(), 'Admin.Actions'),
                'icon' => 'icon-power-off text-danger',
            ),
        );

        if (Shop::isFeatureActive()) {
            if (Shop::getContextShopID() !== null) {
                $this->_join .= ' INNER JOIN ' . _DB_PREFIX_ . 'shop AS shop ON a.id_shop = shop.id_shop AND shop.id_shop = ' . Shop::getContextShopID();
            } else {
                $this->_join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'shop AS shop ON a.id_shop = shop.id_shop';

                $this->fields_list['shop_name'] = array(
                    'title' => $this->l('Shop'),
                    'filter_key' => 'shop!name',
                    'orderby' => true,
                    'filter' => true,
                    'search' => true,
                );

                $this->_select .= ' shop.name AS shop_name,';
            }
        }

        $this->_select .= ' (CASE WHEN (a.image_1 IS NOT NULL AND a.image_1 != "") THEN 1 ELSE 0 END) AS has_image, (CASE WHEN (a.categories IS NOT NULL AND a.categories != "") THEN 1 ELSE 0 END) AS has_categories, (CASE WHEN ((a.part_number IS NOT NULL AND a.part_number != "") OR (a.ean_code IS NOT NULL AND a.ean_code != "")) THEN 1 ELSE 0 END) AS has_code';
    }

    /**
     * {@inheritDoc}
     *
     * @return void
     * @since  1.0.0
     */
    public function initToolBarTitle()
    {
        $this->toolbar_title[] = 'TrovaPrezzi';
    }

    /**
     * {@inheritDoc}
     *
     * @return void
     * @since  1.0.0
     */
    public function initToolbar()
    {
        switch ($this->display) {
            case 'view':
                $back = Tools::safeOutput(Tools::getValue('back', ''));

                if (empty($back)) {
                    $back = self::$currentIndex . '&token=' . $this->token;
                }

                if (!Validate::isCleanHtml($back)) {
                    die(Tools::displayError());
                }

                if (!$this->lite_display) {
                    $this->toolbar_btn['back'] = array(
                        'href' => $back,
                        'desc' => $this->l('Back to list'),
                    );
                }

                break;
            default:
                $this->toolbar_btn['rebuild'] = array(
                    'href' => self::$currentIndex . '&rebuild' . $this->table . '&token=' . $this->token,
                    'desc' => $this->l('Rebuild'),
                    'imgclass' => 'eraser'
                );
                $this->toolbar_btn['generate'] = array(
                    'href' => self::$currentIndex . '&generate' . $this->table . '&token=' . $this->token,
                    'desc' => $this->l('Generate XML'),
                    'imgclass' => 'save'
                );
                $this->toolbar_btn['settings'] = array(
                    'href' => $this->context->link->getAdminLink('AdminModules', true, array(), array('configure' => $this->module->name)),
                    'desc' => $this->l('Settings'),
                    'imgclass' => 'cogs'
                );

                if ($this->allow_export) {
                    $this->toolbar_btn['export'] = array(
                        'href' => self::$currentIndex . '&export' . $this->table . '&token=' . $this->token,
                        'desc' => $this->l('Export'),
                    );
                }

                break;
        }

        $this->addToolBarModulesListButton();
    }

    /**
     * {@inheritDoc}
     *
     * @return void
     * @since  1.0.0
     */
    public function initProcess()
    {
        parent::initProcess();

        if (Tools::getIsset('rebuild' . $this->table)) {
            $this->module->updateProducts();

            Tools::redirectAdmin($this->context->link->getAdminLink('AdminTrovaprezzi'));
        }

        if (Tools::getIsset('generate' . $this->table)) {
            $this->module->generate(false);

            Tools::redirectAdmin($this->context->link->getAdminLink('AdminTrovaprezzi'));
        }

        if (Tools::getIsset('view' . $this->table)) {
            $this->object = new TrovaprezziOffer((int) Tools::getValue('id_pittica_trovaprezzi_offer'));
            
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminProducts', true, array('id_product' => $this->object->id_product)));
        }
    }
}
