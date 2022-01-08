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

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;

/**
 * Trovaprezzi offer class.
 *
 * @category Object
 * @package  Pittica/Trovaprezzi
 * @author   Lucio Benini <info@pittica.com>
 * @license  http://opensource.org/licenses/LGPL-3.0  The GNU Lesser General Public License, version 3.0 ( LGPL-3.0 )
 * @link     https://github.com/pittica/prestashop-trovaprezzi/blob/main/pitticatrovaprezzi.php
 * @since    1.0.0
 */
class TrovaprezziOffer extends ObjectModel
{
    const TABLE_NAME = 'pittica_trovaprezzi_offer';

    public $id_product;
    public $id_product_attribute;
    public $id_shop;
    public $name;
    public $brand;
    public $description;
    public $original_price;
    public $price;
    public $stock;
    public $categories;
    public $image_1;
    public $image_2;
    public $image_3;
    public $part_number;
    public $ean_code;
    public $shipping_cost;
    public $weight;
    public $active;

    public static $definition = array(
        'table' => self::TABLE_NAME,
        'primary' => 'id_pittica_trovaprezzi_offer',
        'multilang' => false,
        'multishop' => true,
        'fields' => array(
            'id_product' => array(
                'type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true
            ),
            'id_product_attribute' => array(
                'type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'required' => false
            ),
            'id_shop' => array(
                'type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true
            ),
            'name' => array(
                'type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true
            ),
            'brand' => array(
                'type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false
            ),
            'description' => array(
                'type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false
            ),
            'original_price' => array(
                'type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => false
            ),
            'price' => array(
                'type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => false
            ),
            'link' => array(
                'type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false
            ),
            'stock' => array(
                'type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => false
            ),
            'categories' => array(
                'type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false
            ),
            'image_1' => array(
                'type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false
            ),
            'image_2' => array(
                'type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false
            ),
            'image_3' => array(
                'type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false
            ),
            'part_number' => array(
                'type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false
            ),
            'ean_code' => array(
                'type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false
            ),
            'shipping_cost' => array(
                'type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => false
            ),
            'weight' => array(
                'type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => false
            ),
            'active' => array(
                'type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => false
            )
        )
    );

    /**
     * Gets the offers.
     *
     * @param int|null $shop Shop ID.
     *
     * @return array
     * @since  1.0.0
     */
    public static function getOffers($shop = null)
    {
        $query = 'SELECT o.id_pittica_trovaprezzi_offer FROM `' . _DB_PREFIX_ . self::TABLE_NAME . '` o';

        if ($shop) {
            $query .= ' WHERE o.id_shop = ' . pSQL((int) $shop);
        }

        $result = Db::getInstance()->executeS($query);
        $offers = array();

        foreach ($result as $row) {
            $offers[] = new static((int)$row['id_pittica_trovaprezzi_offer']);
        }

        return $offers;
    }

    /**
     * Truncates the table.
     *
     * @return boolean
     * @since  1.0.0
     */
    public static function truncate()
    {
        return Db::getInstance()->execute('TRUNCATE `' . _DB_PREFIX_ . self::TABLE_NAME . '`');
    }

    /**
     * Creates an instance from the given product.
     *
     * @param Product $product Product object.
     * @param int     $shop    Shop ID.
     * @param int     $lang    Language ID.
     *
     * @return Offer
     * @since  1.3.2
     */
    public static function fromProduct($product, $shop, $lang)
    {
        $offer = new TrovaprezziOffer();
        
        $offer->id_product  = $product->id;
        $offer->id_shop     = $shop;
        $offer->brand       = $product->manufacturer_name;
        $offer->active      = $product->active;

        $offer->clearDescription($product, $lang);

        return $offer;
    }

    /**
     * Populates the images of the given offer.
     *
     * @param Context $context Offer object.
     * @param Product $product Product object.
     * @param int     $lang    Language ID.
     * @param int     $shop    Shop ID.
     *
     * @return TrovaprezziOffer
     * @since  1.3.2
     */
    public function populateImages($context, $product, $lang, $shop)
    {
        $retriever = new ImageRetriever($context->link);
        $empty     = $retriever->getNoPictureImage(new Language($lang));
        $empty     = !empty($img['large']['url']) ? $img['large']['url'] : '';

        $images  = array();

        foreach (Image::getImages($lang, $this->id_product, $this->id_product_attribute ? $this->id_product_attribute : null, $shop) as $image) {
            $img = $retriever->getImage($product, $image['id_image']);

            if (!empty($img['large']['url'])) {
                $images[] = $img['large']['url'];
            }
        }

        $this->image_2 = !empty($images[1]) ? $images[1] : $empty;
        $this->image_3 = !empty($images[2]) ? $images[2] : $empty;

        if (!empty($images[0])) {
            $this->image_1 = $images[0];
        } else {
            $cover = Image::getGlobalCover($this->id_product);

            if (!empty($cover)) {
                $this->image_1 = $context->link->getImageLink($this->getImageRewrite($product, $lang), (int) $cover['id_image']);
            } else {
                $this->image_1 = $empty;
            }
        }

        return $this;
    }

    /**
     * Calculates and updates the price.
     *
     * @param int      $shop      Shop ID.
     * @param int      $currency  Currency ID.
     * @param int      $quantity  Quantity.
     * @param int      $country   Country ID.
     * @param int      $group     Group ID.
     * @param int      $product   Product ID.
     * @param int|null $attribute Attribute ID.
     *
     * @return TrovaprezziOffer
     * @since  1.3.2
     */
    public function updatePrices($shop, $currency, $quantity, $country, $group, $product, $attribute = null)
    {
        $this->original_price = $this->calculatePrice($shop, $currency, $quantity, $country, $group, $product, $attribute, false);
        $this->price          = $this->calculatePrice($shop, $currency, $quantity, $country, $group, $product, $attribute, true);
        $this->active         = $this->price > 0 ? 1 : 0;

        return $this;
    }

    /**
     * Calculates and updates the price.
     *
     * @param int        $shop      Shop ID.
     * @param int        $currency  Currency ID.
     * @param int        $carrier   Carrier ID.
     * @param Country    $country   Country object.
     * @param int        $lang      Language ID.
     * @param int        $quantity  Quantity ID.
     * @param Product    $product   Product object.
     * @param int|null   $attribute Attribute ID.
     * @param float|null $free      Free shipping limit.
     *
     * @return TrovaprezziOffer
     * @since  1.3.2
     */
    public function updateShippingCost($shop, $currency, $carrier, $country, $lang, $quantity, $product, $attribute = null, $free = null)
    {
        if ($free !== null && $this->price >= (float) $free) {
            $this->shipping_cost = (float) $product->additional_shipping_cost;
        } else {
            $cart              = new Cart(0);
            $cart->id_currency = $currency;
            $cart->id_lang     = $lang;
            $cart->id_carrier  = $carrier;
            $cart->id_shop     = $shop;

            $this->shipping_cost = $cart->getPackageShippingCost(
                $carrier,
                true,
                $country,
                array(
                    array(
                        'id_product'               => $product->id,
                        'id_product_attribute'     => $attribute,
                        'id_shop'                  => $shop,
                        'cart_quantity'            => $quantity,
                        'is_virtual'               => $product->is_virtual,
                        'id_address_delivery'      => null,
                        'id_customization'         => null,
                        'weight'                   => $this->weight,
                        'additional_shipping_cost' => $product->additional_shipping_cost
                    )
                )
            );

            $cart->delete();
        }

        return $this;
    }

    /**
     * Calculates the price.
     *
     * @param int      $shop      Shop ID.
     * @param int      $currency  Currency ID.
     * @param int      $quantity  Quantity ID.
     * @param int      $country   Country ID.
     * @param int      $group     Group ID.
     * @param int      $product   Product ID.
     * @param int|null $attribute Attribute ID.
     * @param boolean  $reduction A value indicating whether use reductions.
     *
     * @return float
     * @since  1.3.2
     */
    protected function calculatePrice($shop, $currency, $quantity, $country, $group, $product, $attribute = null, $reduction = true)
    {
        $price = null;

        $result = Product::priceCalculation(
            $shop,
            $product,
            $attribute,
            $country,
            0,
            0,
            $currency,
            $group,
            $quantity,
            true,
            7,
            false,
            $reduction,
            true,
            $price,
            true,
            0,
            true,
            0,
            $quantity
        );

        return Tools::ps_round($result, 2);
    }

    /**
     * Gets the link rewrite for an image of the given product.
     *
     * @param Product $product Product object.
     * @param int     $lang    Language ID.
     *
     * @return string
     * @since  1.3.2
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
     * @return TrovaprezziOffer
     * @since  1.3.2
     */
    protected function clearDescription($product, $lang)
    {
        $this->description = trim(trim(strip_tags(is_array($product->description_short) ? $product->description_short[$lang] : $product->description_short), PHP_EOL), ' ');

        return $this;
    }
}
