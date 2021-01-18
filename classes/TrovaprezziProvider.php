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

class TrovaprezziProvider extends Provider
{
    public function getElementRoot()
    {
        return 'Products';
    }

    public function getElementItem()
    {
        return 'Offer';
    }

    public function getFilename()
    {
        return 'trovaprezzi';
    }

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
