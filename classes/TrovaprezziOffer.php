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

    public static function getOffers()
    {
        $result = Db::getInstance()->executeS('SELECT o.id_pittica_trovaprezzi_offer FROM `' . _DB_PREFIX_ . self::TABLE_NAME . '` o');
        $offers = array();

        foreach ($result as $row) {
            $offers[] = new static((int)$row['id_pittica_trovaprezzi_offer']);
        }

        return $offers;
    }

    public static function truncate()
    {
        return Db::getInstance()->execute('TRUNCATE `' . _DB_PREFIX_ . self::TABLE_NAME . '`');
    }
}
