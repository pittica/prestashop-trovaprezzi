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

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . TrovaprezziOffer::TABLE_NAME . '` (
	`id_pitticatrovaprezzi_offer` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`id_product` INT(10) UNSIGNED NOT NULL,
	`id_product_attribute` INT(10) UNSIGNED NULL,
	`name` TEXT NOT NULL,
	`brand` TEXT NULL,
	`description` TEXT NULL,
	`original_price` DECIMAL(20,6) NULL,
	`price` DECIMAL(20,6) NULL,
	`link` TEXT NULL,
	`stock` INT(10) UNSIGNED NOT NULL,
	`categories` TEXT NULL,
	`image_1` TEXT NULL,
	`image_2` TEXT NULL,
	`image_3` TEXT NULL,
	`part_number` TEXT NULL,
	`ean_code` TEXT NULL,
	`shipping_cost` DECIMAL(20,6) NULL,
	`weight` DECIMAL(20,6) NULL,
	`active` TINYINT NOT NULL DEFAULT 1,
	PRIMARY KEY (`id_pitticatrovaprezzi_offer`),
	UNIQUE KEY `code` (`id_product`,`id_product_attribute`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
	if (Db::getInstance()->execute($query) == false) {
		return false;
	}
}
