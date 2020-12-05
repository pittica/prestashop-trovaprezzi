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

class TrovaprezziOffer extends ObjectModel
{
	const TABLE_NAME = 'pitticatrovaprezzi_offer';

	public $id_product;
	public $id_product_attribute;
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
		'primary' => 'id_pitticatrovaprezzi_offer',
		'multilang' => false,
		'fields' => array(
			'id_product' => array(
				'type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true, 'lang' => false,
			),
			'id_product_attribute' => array(
				'type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'required' => false, 'lang' => false,
			),
			'name' => array(
				'type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'lang' => false
			),
			'brand' => array(
				'type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'lang' => false
			),
			'description' => array(
				'type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'lang' => false
			),
			'original_price' => array(
				'type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => false, 'lang' => false
			),
			'price' => array(
				'type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => false, 'lang' => false
			),
			'link' => array(
				'type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'lang' => false
			),
			'stock' => array(
				'type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => false, 'lang' => false,
			),
			'categories' => array(
				'type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'lang' => false
			),
			'image_1' => array(
				'type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'lang' => false
			),
			'image_2' => array(
				'type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'lang' => false
			),
			'image_3' => array(
				'type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'lang' => false
			),
			'part_number' => array(
				'type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'lang' => false
			),
			'ean_code' => array(
				'type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false, 'lang' => false
			),
			'shipping_cost' => array(
				'type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => false, 'lang' => false
			),
			'weight' => array(
				'type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => false, 'lang' => false
			),
			'active' => array(
				'type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => false, 'lang' => false
			)
		)
	);

	public static function getOffers()
	{
		$result = Db::getInstance()->executeS('SELECT o.id_pitticatrovaprezzi_offer FROM `' . _DB_PREFIX_ . self::TABLE_NAME . '` o');
		$offers = array();

		foreach ($result as $row) {
			$offers[] = new static((int)$row['id_pitticatrovaprezzi_offer']);
		}

		return $offers;
	}

	public static function truncate()
	{
		return Db::getInstance()->execute('TRUNCATE `' . _DB_PREFIX_ . self::TABLE_NAME . '`');
	}

	public function toArray()
	{
		return array(
			'Name' => $this->name,
			'Brand' => $this->brand,
			'Description' => $this->description,
			'OriginalPrice' => $this->original_price,
			'Price' => $this->price,
			'Code' => $this->id_product . ($this->id_product_attribute ? ('-' . $this->id_product_attribute) : ''),
			'Link' => $this->link,
			'Stock' => $this->stock,
			'Categories' => $this->categories,
			'Image' => $this->image_1,
			'ShippingCost' => $this->shipping_cost,
			'PartNumber' => $this->part_number,
			'EanCode' => $this->ean_code,
			'Weight' => $this->weight,
			'Image2' => $this->image_2,
			'Image3' => $this->image_3
		);
	}
}
