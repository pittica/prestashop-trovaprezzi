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

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__) . '/../../classes/TrovaprezziOffer.php');

class AdminTrovaprezziController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = TrovaprezziOffer::TABLE_NAME;
        $this->lang = false;
        $this->className = TrovaprezziOffer::class;
        $this->display = 'list';
        $this->actions = array('view');
        $this->identifier = 'id_pitticatrovaprezzi_offer';
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

        $this->_select .= ' (CASE WHEN (a.image_1 IS NOT NULL AND a.image_1 != "") THEN 1 ELSE 0 END) AS has_image, (CASE WHEN (a.categories IS NOT NULL AND a.categories != "") THEN 1 ELSE 0 END) AS has_categories, (CASE WHEN ((a.part_number IS NOT NULL AND a.part_number != "") OR (a.ean_code IS NOT NULL AND a.ean_code != "")) THEN 1 ELSE 0 END) AS has_code ';
    }

    public function initToolBarTitle()
    {
        $this->toolbar_title[] = 'TrovaPrezzi';
    }

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
        }

        $this->addToolBarModulesListButton();
    }

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
            $this->object = new TrovaprezziOffer((int)Tools::getValue('id_pitticatrovaprezzi_offer'));
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminProducts', true, array('id_product' => $this->object->id_product)));
        }
    }
}
