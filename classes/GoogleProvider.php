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

require_once(dirname(__FILE__) . '/Provider.php');

class GoogleProvider extends Provider
{
    public function getElementRoot()
    {
        return 'rss';
    }

    public function getElementItem()
    {
        return 'item';
    }

    public function getFilename()
    {
        return 'google';
    }

    public function renderAttributes($xml)
    {
        $xml->writeAttribute('version', '2.0');
        $xml->WriteAttribute('xmlns:g', 'http://base.google.com/ns/1.0');

        return $xml;
    }

    public function renderItem($xml, $offer)
    {
        $context = Context::getContext();
        $carrier = new Carrier((int)Configuration::get('PITTICA_TROVAPREZZI_CARRIER'));
        $locale = Tools::getContextLocale($context);

        $xml->writeElement('title',  $offer->name);
        $xml->writeElement('description',  $offer->description);
        $xml->writeElement('link',  $offer->link);
        $xml->writeElement('g:id', $offer->id_product . ($offer->id_product_attribute ? ('-' . $offer->id_product_attribute) : ''));
        $xml->writeElement('g:image_link', $offer->image_1);
        $xml->writeElement('g:price', $locale->formatPrice($offer->original_price, $context->currency->iso_code));

        if ($offer->original_price != $offer->price) {
            $xml->writeElement('g:sale_price', $locale->formatPrice($offer->price, $context->currency->iso_code));
        }

        $xml->writeElement('g:gtin', !empty($offer->ean_code) ? $offer->ean_code : $offer->part_number);
        $xml->writeElement('g:brand', $offer->brand);
        $xml->writeElement('g:availability', $offer->stock);
        $xml->startElement('g:shipping');
        $xml->writeElement('g:country', Country::getIsoById((int)Configuration::get('PS_COUNTRY_DEFAULT')));
        $xml->writeElement('g:service', $carrier->name);
        $xml->writeElement('g:price', $locale->formatPrice($offer->shipping_cost, $context->currency->iso_code));
        $xml->endElement();

        if (!empty($offer->image_2)) {
            $xml->writeElement('g:additional_image_link', $offer->image_2);
        }

        if (!empty($offer->image_3)) {
            $xml->writeElement('g:additional_image_link', $offer->image_3);
        }

        return $xml;
    }

    public function renderBody($xml)
    {
        $xml->startElement('channel');

        $meta = Meta::getHomeMetas((int)Configuration::get('PS_LANG_DEFAULT'), 'index');

        $xml->writeElement('title', Configuration::get('PS_SHOP_NAME'));
        $xml->writeElement('link', Context::getContext()->link->getBaseLink());
        $xml->writeElement('description', $meta['meta_description']);

        $offers = TrovaprezziOffer::getOffers();

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
