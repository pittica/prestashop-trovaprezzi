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

require_once dirname(__FILE__) . '/classes/TrovaprezziOffer.php';
require_once dirname(__FILE__) . '/classes/Provider.php';

/**
 * Trovaprezzi module class.
 *
 * @category Module
 * @package  Pittica/Trovaprezzi
 * @author   Lucio Benini <info@pittica.com>
 * @license  http://opensource.org/licenses/LGPL-3.0  The GNU Lesser General Public License, version 3.0 ( LGPL-3.0 )
 * @link     https://github.com/pittica/prestashop-trovaprezzi/blob/main/pitticatrovaprezzi.php
 * @since    1.0.0
 */
class PitticaTrovaprezzi extends Module
{
    /**
     * {@inheritDoc}
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->name          = 'pitticatrovaprezzi';
        $this->tab           = 'front_office_features';
        $this->version       = '1.3.3';
        $this->author        = 'Pittica';
        $this->need_instance = 1;
        $this->bootstrap     = 1;

        parent::__construct();

        $this->displayName = $this->l('TrovaPrezzi');
        $this->description = $this->l('Creates an XML feed for TrovaPrezzi.it.');

        $this->ps_versions_compliancy = array(
            'min' => '1.7.7.0',
            'max' => _PS_VERSION_
        );
    }

    /**
     * {@inheritDoc}
     *
     * @return boolean
     * @since  1.0.0
     */
    public function install()
    {
        include(dirname(__FILE__) . '/sql/install.php');

        $carriers = Carrier::getCarriers((int) Configuration::get('PS_LANG_DEFAULT'), true);
        reset($carriers);
        Configuration::updateValue('PITTICA_TROVAPREZZI_CARRIER', !empty($carriers[0]['id_carrier']) ? (int) $carriers[0]['id_carrier'] : -1);

        return parent::install() && $this->installTab() && $this->registerHook('displayFooterAfter');
    }
    
    /**
     * {@inheritDoc}
     *
     * @return boolean
     * @since  1.0.0
     */
    public function uninstall()
    {
        include(dirname(__FILE__) . '/sql/uninstall.php');

        return parent::uninstall() && $this->uninstallTab() && Configuration::deleteByName('PITTICA_TROVAPREZZI_CARRIER');
    }

    /**
     * Registers the controller tab.
     *
     * @return boolean
     * @since  1.0.0
     */
    public function installTab()
    {
        $id = (int) Tab::getIdFromClassName('AdminTrovaprezzi');

        if (!$id) {
            $id = null;
        }

        $tab             = new Tab($id);
        $tab->active     = 1;
        $tab->class_name = 'AdminTrovaprezzi';
        $tab->name       = array();

        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = 'AdminTrovaprezzi';
        }

        $tab->module = $this->name;

        return $tab->add();
    }

    /**
     * Registers the controller tab.
     *
     * @return boolean
     * @since  1.0.0
     */
    public function uninstallTab()
    {
        $id = (int) Tab::getIdFromClassName('AdminTrovaprezzi');

        if ($id) {
            $tab = new Tab($id);

            return $tab->delete();
        }

        return false;
    }

    /**
     * {@inheritDoc}
     *
     * @return string
     * @since  1.0.0
     */
    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('savepitticatrovaprezzi')) {
            Configuration::updateValue('PITTICA_TROVAPREZZI_CARRIER', Tools::getValue('carrier'));

            $output .= $this->displayConfirmation($this->l('Settings updated.'));
        }

        return $output . $this->renderForm();
    }

    /**
     * Renders settings form.
     *
     * @return void
     * @since  1.0.0
     */
    protected function renderForm()
    {
        $lang     = (int) Configuration::get('PS_LANG_DEFAULT');
        $carriers = Carrier::getCarriers($lang, true);

        $helper                           = new HelperForm();
        $helper->module                   = $this;
        $helper->name_controller          = 'pitticatrovaprezzi';
        $helper->identifier               = $this->identifier;
        $helper->token                    = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex             = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language    = $lang;
        $helper->allow_employee_form_lang = $lang;
        $helper->title                    = $this->displayName;
        $helper->submit_action            = 'savepitticatrovaprezzi';

        $check    = $this->context->link->getAdminLink('AdminTrovaprezzi');

        $helper->fields_value = array(
            'generate'             => $this->getGenerateFeedHtml(null),
            'feed_trovaprezzi'     => $this->getViewFeedHtml('trovaprezzi'),
            'generate_trovaprezzi' => $this->getGenerateFeedHtml('trovaprezzi'),
            'feed_google'          => $this->getViewFeedHtml('google'),
            'generate_google'      => $this->getGenerateFeedHtml('google'),
            'carrier'              => Configuration::get('PITTICA_TROVAPREZZI_CARRIER'),
            'check'                => '<a href="' . $check . '">' . $this->l('Check non-compliant products.') . '</a>'
        );

        return $helper->generateForm(
            array(
                array(
                    'form' => array(
                        'legend' => array(
                            'title' => $this->l('Settings')
                        ),
                        'input' => array(
                            array(
                                'type'  => 'select',
                                'label' => $this->l('Carrier:'),
                                'name'  => 'carrier',
                                'options' => array(
                                    'query' => $carriers,
                                    'id'    => 'id_carrier',
                                    'name'  => 'name'
                                )
                            ),
                            array(
                                'type'  => 'free',
                                'label' => $this->l('Check Products'),
                                'name'  => 'check'
                            )
                        ),
                        'submit' => array(
                            'title' => $this->l('Save')
                        )
                    )
                ),
                array(
                    'form' => array(
                        'legend' => array(
                            'title' => $this->l('Feed Generator')
                        ),
                        'input' => array(
                            array(
                                'type'  => 'free',
                                'label' => $this->l('Generator URL'),
                                'name'  => 'generate'
                            )
                        ),
                        'submit' => array(
                            'title' => $this->l('Save')
                        )
                    )
                ),
                array(
                    'form' => array(
                        'legend' => array(
                            'title' => $this->l('Trovaprezzi')
                        ),
                        'input' => array(
                            array(
                                'type'  => 'free',
                                'label' => $this->l('Feed URL'),
                                'name'  => 'feed_trovaprezzi'
                            ),
                            array(
                                'type'  => 'free',
                                'label' => $this->l('Generator URL'),
                                'name'  => 'generate_trovaprezzi'
                            )
                        ),
                        'submit' => array(
                            'title' => $this->l('Save')
                        )
                    )
                ),
                array(
                    'form' => array(
                        'legend' => array(
                            'title' => $this->l('Google')
                        ),
                        'input' => array(
                            array(
                                'type'  => 'free',
                                'label' => $this->l('Feed URL'),
                                'name'  => 'feed_google'
                            ),
                            array(
                                'type'  => 'free',
                                'label' => $this->l('Generator URL'),
                                'name'  => 'generate_google'
                            )
                        ),
                        'submit' => array(
                            'title' => $this->l('Save')
                        )
                    )
                )
            )
        );
    }

    /**
     * Handles the displayFooterAfter hook.
     *
     * @param array $params Hook parameters.
     *
     * @return mixed
     * @since  1.0.0
     */
    public function hookDisplayFooterAfter($params)
    {
        return $this->display(__FILE__, 'displayFooterAfter.tpl');
    }

    /**
     * Gets the file path of the XML feeds.
     *
     * @param string $file    Filename.
     * @param int    $id_shop Shop ID.
     *
     * @return string
     * @since  1.0.0
     */
    public function getFilePath($file = 'trovaprezzi', $id_shop = null)
    {
        return _PS_MODULE_DIR_ . $this->name . DIRECTORY_SEPARATOR . 'output' . DIRECTORY_SEPARATOR . ($id_shop === null ? Shop::getContextShopID() : (int) $id_shop) . '_' . $file . '.xml';
    }

    /**
     * Generates the secuirty token.
     *
     * @return string
     * @since  1.0.0
     */
    public function getToken()
    {
        return Tools::hash(Configuration::get('PS_SHOP_DOMAIN'));
    }

    /**
     * Updates the products data.
     *
     * @param int $id_shop Shop ID.
     *
     * @return void
     * @since  1.0.0
     */
    public function updateProducts($id_shop = null)
    {
        TrovaprezziOffer::truncate();

        $lang     = (int) Configuration::get('PS_LANG_DEFAULT');
        $root     = (int) Configuration::get('PS_ROOT_CATEGORY');
        $home     = (int) Configuration::get('PS_HOME_CATEGORY');
        $currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');
        $carrier  = (int) Configuration::get('PITTICA_TROVAPREZZI_CARRIER');
        $group    = (int) Configuration::get('PS_GUEST_GROUP');
        $country  = new Country((int) Configuration::get('PS_COUNTRY_DEFAULT'));
        $products = Product::getProducts($lang, 0, 0, 'id_product', 'ASC', false, true);
        $shops    = $id_shop === null ? Shop::getShops(true, null, true) : array($id_shop);
        
        foreach ($shops as $shop) {
            foreach ($products as $p) {
                $product    = new Product((int) $p['id_product'], $lang, (int) $shop);
                $attributes = $product->getAttributesResume($lang, ': ');
                $categories = array();
                $cat        = '';

                foreach (Product::getProductCategoriesFull($product->id, $lang) as $category) {
                    if ($category['id_category'] != $root && $category['id_category'] != $home) {
                        $categories[] = $category['name'];
                    }

                    if ($category['id_category'] == $product->id_category_default) {
                        $cat = $category['link_rewrite'];
                    }
                }

                if (!empty($attributes)) {
                    foreach ($attributes as $attribute) {
                        if ((int) $attribute['quantity'] > 0) {
                            $minimal = (!empty($attribute['minimal_quantity']) && $attribute['minimal_quantity'] > 0) ? (int) $attribute['minimal_quantity'] : 1;

                            $ean   = empty($attribute['ean13']) ? $product->ean13 : $attribute['ean13'];
                            $offer = TrovaprezziOffer::fromProduct($product, $shop, $lang);

                            $offer->updatePrices($shop, $currency, $minimal, $country->id, $group, $product->id, (int) $attribute['id_product_attribute']);

                            $offer->id_product_attribute = (int) $attribute['id_product_attribute'];
                            $offer->name                 = (is_array($product->name) ? $product->name[$lang] : $product->name) . ' - ' . $attribute['attribute_designation'];
                            $offer->link                 = $this->context->link->getProductLink($product, null, $cat, null, null, $shop, (int) $attribute['id_product_attribute']);
                            $offer->stock                = (int) $attribute['quantity'];
                            $offer->categories           = implode(', ', $categories);
                            $offer->weight               = (float) $attribute['weight'] + (float) $product->weight;
                            $offer->part_number          = empty($attribute['reference']) ? $ean : $attribute['reference'];
                            $offer->ean_code             = $ean;

                            $offer->updateShippingCost($shop, $currency, $carrier, $country, $lang, $minimal, $product, (int) $attribute['id_product_attribute']);
                            $offer->populateImages($this->context, $product, $lang, $shop);

                            $offer->add();
                        }
                    }
                } else {
                    if ((int) $product->quantity) {
                        $minimal = $product->minimal_quantity > 0 ? (int) $product->minimal_quantity : 1;

                        $offer = TrovaprezziOffer::fromProduct($product, $shop, $lang);

                        $offer->updatePrices($shop, $currency, $minimal, $country->id, $group, $product->id);

                        $offer->name        = is_array($product->name) ? $product->name[$lang] : $product->name;
                        $offer->link        = $this->context->link->getProductLink($product, null, $cat, null, null, $shop);
                        $offer->stock       = (int) $product->quantity;
                        $offer->categories  = implode(', ', $categories);
                        $offer->weight      = (float) $product->weight;
                        $offer->part_number = empty($product->reference) ? $product->ean13 : $product->reference;
                        $offer->ean_code    = $product->ean13;

                        $offer->updateShippingCost($shop, $currency, $carrier, $country, $lang, $minimal, $product);
                        $offer->populateImages($this->context, $product, $lang, $shop);

                        $offer->add();
                    }
                }
            }
        }
    }

    /**
     * Generates the feeds.
     *
     * @param boolean  $refresh  A value indicating whether the data requires to be refreshed.
     * @param Provider $provider Feed provider.
     * @param int      $id_shop  Shop ID.
     *
     * @return boolean Returns "True" whether the XML file has been generated; otherwise, "False".
     * @since  1.0.0
     */
    public function generate($refresh = true, $provider = null, $id_shop = null)
    {
        if ($refresh) {
            $this->updateProducts();
        }

        $providers = Provider::getProviders();
        $shops     = $id_shop === null ? Shop::getShops(true, null, true) : array($id_shop);

        if ($provider && in_array($provider, $providers)) {
            $providers = array($provider);
        }

        foreach ($providers as $p) {
            $object = Provider::getProvider($p);

            foreach ($shops as $shop) {
                $object->generate($this->getFilePath($p, $shop), $shop);
            }
        }

        return true;
    }

    /**
     * Generates the HTML of the generator link in the configuration form.
     *
     * @param Provider $provider Feed provider.
     *
     * @return void
     * @since  1.0.0
     */
    protected function getGenerateFeedHtml($provider)
    {
        $url = $this->context->link->getModuleLink(
            $this->name,
            'generate',
            array(
                'provider' => $provider,
                'token'    => $this->getToken()
            )
        );

        return $this->l('Use this link to generate the XML Feed:') . '<br/><a href="' . $url . '" target="_system">' . $url . '</a>';
    }

    /**
     * Generates the HTML of the view link in the configuration form.
     *
     * @param Provider $provider Feed provider.
     *
     * @return void
     * @since  1.0.0
     */
    protected function getViewFeedHtml($provider)
    {
        $url = $this->context->link->getModuleLink(
            $this->name,
            'download',
            array(
                'provider' => $provider,
                'token'    => $this->getToken()
            )
        );

        return $this->l('XML Feed URL:') . '<br/><a href="' . $url . '" target="_system">' . $url . '</a>';
    }
}
