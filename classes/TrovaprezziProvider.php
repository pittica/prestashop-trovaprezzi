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

require_once dirname(__FILE__) . '/Provider.php';

/**
 * Trovaprezzi provider class.
 *
 * @category Provider
 * @package  Pittica/Trovaprezzi
 * @author   Lucio Benini <info@pittica.com>
 * @license  http://opensource.org/licenses/LGPL-3.0  The GNU Lesser General Public License, version 3.0 ( LGPL-3.0 )
 * @link     https://github.com/pittica/prestashop-trovaprezzi/blob/main/classes/TrovaprezziProvider.php
 * @since    1.2.0
 */
class TrovaprezziProvider extends Provider
{
    /**
     * {@inheritDoc}
     * 
     * @return string
     * @since  1.2.0
     */
    public function getElementRoot()
    {
        return 'Products';
    }

    /**
     * {@inheritDoc}
     * 
     * @return string
     * @since  1.2.0
     */
    public function getElementItem()
    {
        return 'Offer';
    }

    /**
     * {@inheritDoc}
     * 
     * @return string
     * @since  1.2.0
     */
    public function getFilename()
    {
        return 'trovaprezzi';
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
        $xml->writeElement('Name', $offer->name);
        $xml->writeElement('Brand', $offer->brand);
        $xml->writeElement('Description', $offer->description);
        $xml->writeElement('OriginalPrice', $offer->original_price);
        $xml->writeElement('Price', $offer->price);
        $xml->writeElement('Code', $offer->id_product . ($offer->id_product_attribute ? ('-' . $offer->id_product_attribute) : ''));
        $xml->writeElement('Link', $offer->link);
        $xml->writeElement('Stock', $offer->stock);
        $xml->writeElement('Categories', $offer->categories);
        $xml->writeElement('Image', $offer->image_1);
        $xml->writeElement('ShippingCost', $offer->shipping_cost);
        $xml->writeElement('PartNumber', $offer->part_number);
        $xml->writeElement('EanCode', $offer->ean_code);
        $xml->writeElement('Weight', $offer->weight);
        $xml->writeElement('Image2', $offer->image_2);
        $xml->writeElement('Image3', $offer->image_3);

        return $xml;
    }
}
