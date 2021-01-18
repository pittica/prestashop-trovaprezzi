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

require_once(dirname(__FILE__) . '/TrovaprezziOffer.php');

abstract class Provider
{
	public static function getProvider($name)
	{
		$class = ucfirst($name) . 'Provider';

		require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . $class . '.php');

		return new $class;
	}

	public static function getProviders()
	{
		return array(
			'trovaprezzi',
			'google'
		);
	}

	public abstract function getFilename();

	public abstract function getElementRoot();

	public abstract function getElementItem();

	public function renderAttributes($xml)
	{
		return $xml;
	}

	public abstract function renderItem($xml, $offer);

	public function renderBody($xml)
	{
		$offers = TrovaprezziOffer::getOffers();

		foreach ($offers as $offer) {
			if ($offer->active) {
				$xml->startElement($this->getElementItem());

				$this->renderItem($xml, $offer);

				$xml->endElement();
			}
		}

		return $xml;
	}

	public function generate($path)
	{
		$xml = new XmlWriter();
		$xml->openUri($path);
		$xml->startDocument('1.0', 'UTF-8');
		$xml->startElement($this->getElementRoot());

		$this->renderAttributes($xml);
		$this->renderBody($xml);

		$xml->endElement();
		$xml->endDocument();
		$xml->flush();

		return true;
	}
}
