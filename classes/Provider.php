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

require_once dirname(__FILE__) . '/TrovaprezziOffer.php';

/**
 * Base provider class.
 *
 * @category Provider
 * @package  Pittica/Trovaprezzi
 * @author   Lucio Benini <info@pittica.com>
 * @license  http://opensource.org/licenses/LGPL-3.0  The GNU Lesser General Public License, version 3.0 ( LGPL-3.0 )
 * @link     https://github.com/pittica/prestashop-trovaprezzi/blob/main/classes/Provider.php
 * @since    1.2.0
 */
abstract class Provider
{
    /**
     * Gets the provider with the given name.
     *
     * @param string $name The provider's name.
     *
     * @return Provider
     */
    public static function getProvider($name)
    {
        $class = ucfirst($name) . 'Provider';

        include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . $class . '.php';

        return new $class;
    }

    /**
     * Gets the available providers.
     *
     * @return array
     */
    public static function getProviders()
    {
        return array(
            'trovaprezzi',
            'google'
        );
    }

    /**
     * Gets the filename.
     *
     * @return string
     * @since  1.2.0
     */
    abstract public function getFilename();

    /**
     * Gets the element root name.
     *
     * @return string
     * @since  1.2.0
     */
    abstract public function getElementRoot();

    /**
     * Gets the element item name.
     *
     * @return string
     * @since  1.2.0
     */
    abstract public function getElementItem();

    /**
     * Renders the element attributes.
     *
     * @param XmlWriter $xml XML object.
     *
     * @return XmlWriter
     * @since  1.2.0
     */
    public function renderAttributes($xml)
    {
        return $xml;
    }

    /**
     * Renders an offer object.
     *
     * @param XmlWriter        $xml   XML object.
     * @param TrovaprezziOffer $offer Offer item.
     *
     * @return XmlWriter
     * @since  1.2.0
     */
    abstract public function renderItem($xml, $offer);

    /**
     * Renders the XML body.
     *
     * @param XmlWriter $xml  XML object.
     * @param int|null  $shop Shop ID.
     *
     * @return XmlWriter
     * @since  1.2.0
     */
    public function renderBody($xml, $shop = null)
    {
        $offers = TrovaprezziOffer::getOffers($shop);

        foreach ($offers as $offer) {
            if ($offer->active) {
                $xml->startElement($this->getElementItem());

                $this->renderItem($xml, $offer);

                $xml->endElement();
            }
        }

        return $xml;
    }

    /**
     * Writes the XML document.
     *
     * @param string   $path Document path.
     * @param int|null $shop Shop ID.
     *
     * @return boolean
     * @since  1.2.0
     */
    public function generate($path, $shop = null)
    {
        $xml = new XmlWriter();
        $xml->openUri($path);
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement($this->getElementRoot());

        $this->renderAttributes($xml);
        $this->renderBody($xml, $shop);

        $xml->endElement();
        $xml->endDocument();
        $xml->flush();

        return true;
    }
}
