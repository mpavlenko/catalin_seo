<?php

/**
 * Catalin Ciobanu
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @package     Catalin_Seo
 * @copyright   Copyright (c) 2013 Catalin Ciobanu
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Catalin_SEO_Helper_Data extends Mage_Core_Helper_Data
{
    /**
     * Delimiter for multiple filters
     */

    const MULTIPLE_FILTERS_DELIMITER = ',';

    /**
     * Check if module is enabled or not
     * 
     * @return boolean
     */
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag('catalin_seo/catalog/enabled');
    }

    /**
     * Check if ajax is enabled
     * 
     * @return boolean
     */
    public function isAjaxEnabled()
    {
        if (!$this->isEnabled()) {
            return false;
        }
        return Mage::getStoreConfigFlag('catalin_seo/catalog/ajax_enabled');
    }

    /**
     * Check if multipe choice filters is enabled
     * 
     * @return boolean
     */
    public function isMultipleChoiceFiltersEnabled()
    {
        if (!$this->isEnabled()) {
            return false;
        }
        return Mage::getStoreConfigFlag('catalin_seo/catalog/multiple_choise_filters');
    }

    /**
     * Check if price slider is enabled
     * 
     * @return boolean
     */
    public function isPriceSliderEnabled()
    {
        if (!$this->isEnabled()) {
            return false;
        }
        return Mage::getStoreConfigFlag('catalin_seo/catalog/price_slider');
    }

    /**
     * Retrieve price slider delay in seconds.
     * 
     * @return integer
     */
    public function getPriceSliderDelay()
    {
        return Mage::getStoreConfig('catalin_seo/catalog/price_slider_delay');
    }
    
    /**
     * Retrieve how price slider will be submitted (button or delayed auto submit)
     * 
     * @return int
     */
    public function getPriceSliderSubmitType()
    {
        return (int) Mage::getStoreConfig('catalin_seo/catalog/price_slider_submit_type');
    }

    /**
     * Retrieve routing suffix
     * 
     * @return string
     */
    public function getRoutingSuffix()
    {
        return '/' . Mage::getStoreConfig('catalin_seo/catalog/routing_suffix');
    }

    /**
     * Getter for layered navigation params
     * If $params are provided then it overrides the ones from registry
     * 
     * @param array $params
     * @return array|null
     */
    public function getCurrentLayerParams(array $params = null)
    {
        $layerParams = Mage::registry('layer_params');

        if (!is_array($layerParams)) {
            $layerParams = array();
        }

        if (!empty($params)) {
            foreach ($params as $key => $value) {
                if ($value === null) {
                    unset($layerParams[$key]);
                } else {
                    $layerParams[$key] = $value;
                }
            }
        }

        // Sort by key - small SEO improvement
        ksort($layerParams);
        return $layerParams;
    }

    /**
     * Method to get url for layered navigation
     * 
     * @param array $filters      array with new filter values
     * @param integer $escape     to autoescape or not
     * @param boolean $noFilters  to add filters to the url or not
     * @param array $q            array with values to add to query string
     * @return string
     */
    public function getFilterUrl(array $filters, $noFilters = false, array $q = array())
    {
        $query = array(
            'isLayerAjax' => null, // this needs to be removed because of ajax request
            Mage::getBlockSingleton('page/html_pager')->getPageVarName() => null // exclude current page from urls
        );
        $query = array_merge($query, $q);

        $suffix = Mage::getStoreConfig('catalog/seo/category_url_suffix');
        $params = array(
            '_current' => true,
            '_use_rewrite' => true,
            '_query' => $query,
            '_escape' => true,
        );

        $url = Mage::getUrl('*/*/*', $params);
        $urlPath = '';

        if (!$noFilters) {
            // Add filters
            $layerParams = $this->getCurrentLayerParams($filters);
            foreach ($layerParams as $key => $value) {
                // Encode and replace escaped delimiter with the delimiter itself
                $value = str_replace(urlencode(self::MULTIPLE_FILTERS_DELIMITER), self::MULTIPLE_FILTERS_DELIMITER, urlencode($value));
                $urlPath .= "/{$key}/{$value}";
            }
        }
        
        // Skip adding routing suffix for links with no filters
        if (empty($urlPath)) {
            return $url;
        }

        $urlParts = explode('?', $url);

        $urlParts[0] = substr($urlParts[0], 0, strlen($urlParts[0]) - strlen($suffix));
        // Add the suffix to the url - fixes when comming from non suffixed pages
        // It should always be the last bits in the URL
        $urlParts[0] .= $this->getRoutingSuffix();

        $url = $urlParts[0] . $urlPath . $suffix;
        if (!empty($urlParts[1])) {
            $url .= '?' . $urlParts[1];
        }

        return $url;
    }

    /**
     * Get the url to clear all layered navigation filters
     * 
     * @return string
     */
    public function getClearFiltersUrl()
    {
        return $this->getFilterUrl(array(), true);
    }

    /**
     * Get url for layered navigation pagination
     * 
     * @param array $query
     * @return string
     */
    public function getPagerUrl(array $query)
    {
        return $this->getFilterUrl(array(), false, $query);
    }

    /**
     * Check if we are in the catalog search
     * 
     * @return boolean
     */
    public function isCatalogSearch()
    {
        $pathInfo = $this->_getRequest()->getPathInfo();
        if (stripos($pathInfo, '/catalogsearch/result') !== false) {
            return true;
        }
        return false;
    }

}
