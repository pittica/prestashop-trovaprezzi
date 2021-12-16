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

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;

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
        $this->version       = '1.3.1';
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
     */
    public function getFilePath($file = 'trovaprezzi', $id_shop = null)
    {
        return _PS_MODULE_DIR_ . $this->name . DIRECTORY_SEPARATOR . 'output' . DIRECTORY_SEPARATOR . ($id_shop === null ? Shop::getContextShopID() : (int) $id_shop) . '_' . $file . '.xml';
    }

    /**
     * Generates the secuirty token.
     *
     * @return string
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
     */
    public function updateProducts($id_shop = null)
    {
        TrovaprezziOffer::truncate();

        $lang     = (int) Configuration::get('PS_LANG_DEFAULT');
        $root     = (int) Configuration::get('PS_ROOT_CATEGORY');
        $home     = (int) Configuration::get('PS_HOME_CATEGORY');
        $currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');
        $carrier  = (int) Configuration::get('PITTICA_TROVAPREZZI_CARRIER');
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
                            $cart    = $this->getCart($currency, $carrier, $lang, $shop);
                            $minimal = (!empty($attribute['minimal_quantity']) && $attribute['minimal_quantity'] > 0) ? (int) $attribute['minimal_quantity'] : 1;

                            $cart->updateQty($minimal, $product->id, (int) $attribute['id_product_attribute']);

                            $ean = empty($attribute['ean13']) ? $product->ean13 : $attribute['ean13'];

                            $offer                       = new TrovaprezziOffer();
                            $offer->id_product           = $product->id;
                            $offer->id_product_attribute = (int) $attribute['id_product_attribute'];
                            $offer->id_shop              = $shop;
                            $offer->name                 = (is_array($product->name) ? $product->name[$lang] : $product->name) . ' - ' . $attribute['attribute_designation'];
                            $offer->brand                = $product->manufacturer_name;
                            $offer->description          = $this->clearDescription($product, $lang);
                            $offer->original_price       = $product->getPrice(true, (int) $attribute['id_product_attribute'], 2, null, false, false, $minimal);
                            $offer->price                = $product->getPrice(true, (int) $attribute['id_product_attribute'], 2, null, false, true, $minimal);
                            $offer->link                 = $this->context->link->getProductLink($product, null, $cat, null, null, $shop, (int) $attribute['id_product_attribute']);
                            $offer->stock                = (int) $attribute['quantity'];
                            $offer->categories           = implode(', ', $categories);
                            $offer->shipping_cost        = $cart->getPackageShippingCost($carrier, true, $country);
                            $offer->part_number          = empty($attribute['reference']) ? $ean : $attribute['reference'];
                            $offer->ean_code             = $ean;
                            $offer->weight               = (float) $attribute['weight'] + (float) $product->weight;
                            $offer->active               = $product->active;

                            $this->populateImages($offer, $product, $lang, $shop);

                            $offer->add();

                            $cart->delete();
                        }
                    }
                } else {
                    if ((int) $product->quantity) {
                        $cart    = $this->getCart($currency, $carrier, $lang, $shop);
                        $minimal = $product->minimal_quantity > 0 ? (int) $product->minimal_quantity : 1;

                        $cart->updateQty($minimal, $product->id);

                        $offer                 = new TrovaprezziOffer();
                        $offer->id_product     = $product->id;
                        $offer->id_shop        = $shop;
                        $offer->name           = is_array($product->name) ? $product->name[$lang] : $product->name;
                        $offer->brand          = $product->manufacturer_name;
                        $offer->description    = $this->clearDescription($product, $lang);
                        $offer->original_price = $product->getPrice(true, null, 2, null, false, false, $minimal);
                        $offer->price          = $product->getPrice(true, null, 2, null, false, true, $minimal);
                        $offer->link           = $this->context->link->getProductLink($product, null, $cat, null, null, $shop);
                        $offer->stock          = (int) $product->quantity;
                        $offer->categories     = implode(', ', $categories);
                        $offer->shipping_cost  = $cart->getPackageShippingCost($carrier, true, $country);
                        $offer->part_number    = empty($product->reference) ? $product->ean13 : $product->reference;
                        $offer->ean_code       = $product->ean13;
                        $offer->weight         = (float) $product->weight;
                        $offer->active         = $product->active;

                        $this->populateImages($offer, $product, $lang, $shop);

                        $offer->add();

                        $cart->delete();
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
     * Populates the images of the given offer.
     *
     * @param TrovaprezziOffer $offer   Offer object.
     * @param Product          $product Product object.
     * @param int              $lang    Language ID.
     * @param int              $shop    Shop ID.
     *
     * @return TrovaprezziOffer
     */
    protected function populateImages($offer, $product, $lang, $shop)
    {
        $retriever = new ImageRetriever($this->context->link);
        $empty     = $retriever->getNoPictureImage(new Language($lang));
        $empty     = !empty($img['large']['url']) ? $img['large']['url'] : '';

        $images  = array();

        foreach (Image::getImages($lang, $product->id, $offer->id_product_attribute ? $offer->id_product_attribute : null, $shop) as $image) {
            $img = $retriever->getImage($product, $image['id_image']);

            if (!empty($img['large']['url'])) {
                $images[] = $img['large']['url'];
            }
        }

        $offer->image_2 = !empty($images[1]) ? $images[1] : $empty;
        $offer->image_3 = !empty($images[2]) ? $images[2] : $empty;

        if (!empty($images[0])) {
            $offer->image_1 = $images[0];
        } else {
            $cover = Image::getGlobalCover($product->id);

            if (!empty($cover)) {
                $offer->image_1 = $this->context->link->getImageLink($this->getImageRewrite($product, $lang), (int) $cover['id_image']);
            } else {
                $offer->image_1 = $empty;
            }
        }

        return $offer;
    }

    /**
     * Gets the cart object.
     *
     * @param int $currency Currency ID.
     * @param int $carrier  Carrier ID.
     * @param int $lang     Language ID.
     * @param int $shop     Shop ID.
     *
     * @return Cart
     */
    protected function getCart($currency, $carrier, $lang, $shop)
    {
        $cart              = new Cart(0);
        $cart->id_currency = $currency;
        $cart->id_lang     = $lang;
        $cart->id_carrier  = $carrier;
        $cart->id_shop     = $shop;

        return $cart;
    }

    /**
     * Gets the link rewrite for an image of the given product.
     *
     * @param Product $product Product object.
     * @param int     $lang    Language ID.
     *
     * @return string
     */
    protected function getImageRewrite($product, $lang)
    {
        if (!empty($product->link_rewrite)) {
            return is_array($product->link_rewrite) && !empty($product->link_rewrite[$lang]) ? $product->link_rewrite[$lang] : $product->link_rewrite;
        } else {
            return is_array($product->name) && !empty($product->name[$lang]) ? $product->name[$lang] : $product->name;
        }
    }

    /**
     * Clears the product description.
     *
     * @param Product $product Product object.
     * @param int     $lang    Language ID.
     *
     * @return string
     */
    protected function clearDescription($product, $lang)
    {
        return trim(trim(strip_tags(is_array($product->description_short) ? $product->description_short[$lang] : $product->description_short), PHP_EOL), ' ');
    }

    /**
     * Generates the HTML of the generator link in the configuration form.
     *
     * @param Provider $provider Feed provider.
     *
     * @return void
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
