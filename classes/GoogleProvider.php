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

require_once(dirname(__FILE__) . '/Provider.php');

/**
 * Google provider class.
 *
 * @category Provider
 * @package  Pittica/Trovaprezzi
 * @author   Lucio Benini <info@pittica.com>
 * @license  http://opensource.org/licenses/LGPL-3.0  The GNU Lesser General Public License, version 3.0 ( LGPL-3.0 )
 * @link     https://github.com/pittica/prestashop-trovaprezzi/blob/main/classes/GoogleProvider.php
 * @since    1.2.0
 */
class GoogleProvider extends Provider
{
    protected $country;
    protected $context;
    protected $carrier;
    protected $locale;

    /**
     * {@inheritDoc}
     *
     * @since 1.2.0
     */
    public function __construct()
    {
        $this->country = Country::getIsoById((int)Configuration::get('PS_COUNTRY_DEFAULT'));
        $this->context = Context::getContext();
        $this->carrier = new Carrier((int)Configuration::get('PITTICA_TROVAPREZZI_CARRIER'));
        $this->locale = Tools::getContextLocale($this->context);
    }

    /**
     * {@inheritDoc}
     *
     * @return string
     * @since  1.2.0
     */
    public function getElementRoot()
    {
        return 'rss';
    }

    /**
     * {@inheritDoc}
     *
     * @return string
     * @since  1.2.0
     */
    public function getElementItem()
    {
        return 'item';
    }

    /**
     * {@inheritDoc}
     *
     * @return string
     * @since  1.2.0
     */
    public function getFilename()
    {
        return 'google';
    }

    /**
     * {@inheritDoc}
     *
     * @param XmlWriter $xml XML object.
     *
     * @return XmlWriter
     * @since  1.2.0
     */
    public function renderAttributes($xml)
    {
        $xml->writeAttribute('version', '2.0');
        $xml->WriteAttribute('xmlns:g', 'http://base.google.com/ns/1.0');

        return $xml;
    }

    /**
     * {@inheritDoc}
     *
     * @param XmlWriter        $xml   XML object.
     * @param TrovaprezziOffer $offer Offer item.
     *
     * @return XmlWriter
     * @since  1.2.0
     */
    public function renderItem($xml, $offer)
    {
        $xml->writeElement('title', $offer->name);
        $xml->writeElement('description', !empty($offer->description) ? $offer->description : $offer->name);
        $xml->writeElement('link', $offer->link);
        $xml->writeElement('g:id', $offer->id_product . ($offer->id_product_attribute ? ('-' . $offer->id_product_attribute) : ''));
        $xml->writeElement('g:image_link', $offer->image_1);
        $xml->writeElement('g:price', $this->locale->formatPrice($offer->original_price, $this->context->currency->iso_code));

        if ($offer->original_price != $offer->price) {
            $xml->writeElement('g:sale_price', $this->locale->formatPrice($offer->price, $this->context->currency->iso_code));
        }

        $xml->writeElement('g:gtin', !empty($offer->ean_code) ? $offer->ean_code : $offer->part_number);
        $xml->writeElement('g:brand', $offer->brand);
        $xml->writeElement('g:availability', $offer->stock > 0 ? 'in stock' : 'out of stock');
        $xml->startElement('g:shipping');
        $xml->writeElement('g:country', $this->country);
        $xml->writeElement('g:service', $this->carrier->name);
        $xml->writeElement('g:price', $this->locale->formatPrice($offer->shipping_cost, $this->context->currency->iso_code));
        $xml->endElement();

        if (!empty($offer->image_2)) {
            $xml->writeElement('g:additional_image_link', $offer->image_2);
        }

        if (!empty($offer->image_3)) {
            $xml->writeElement('g:additional_image_link', $offer->image_3);
        }

        return $xml;
    }

    /**
     * {@inheritDoc}
     *
     * @param XmlWriter $xml  XML object.
     * @param int|null  $shop Shop ID.
     *
     * @return XmlWriter
     * @since  1.2.0
     */
    public function renderBody($xml, $shop = null)
    {
        $xml->startElement('channel');

        $meta = Meta::getHomeMetas((int)Configuration::get('PS_LANG_DEFAULT'), 'index');

        $xml->writeElement('title', Configuration::get('PS_SHOP_NAME'));
        $xml->writeElement('link', $this->context->link->getBaseLink($shop));
        $xml->writeElement('description', $meta['meta_description']);

        $offers = TrovaprezziOffer::getOffers($shop);

        foreach ($offers as $offer) {
            if ($offer->active) {
                $xml->startElement($this->getElementItem());

                $this->renderItem($xml, $offer);

                $xml->endElement();
            }
        }

        $xml->endElement();
    }
}
