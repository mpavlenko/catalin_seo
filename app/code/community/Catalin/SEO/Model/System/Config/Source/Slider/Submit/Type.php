<?php

class Catalin_SEO_Model_System_Config_Source_Slider_Submit_Type
{

    const SUBMIT_AUTO_DELAYED = 1;
    const SUBMIT_BUTTON = 2;

    protected $_options;

    /**
     * Retrieve types of submit for price slider filter
     * 
     * @return array
     */
    public function toOptionArray()
    {
        if (null === $this->_options) {
            $helper = Mage::helper('catalin_seo');
            $this->_options = array(
                self::SUBMIT_AUTO_DELAYED => $helper->__('Delayed auto submit'),
                self::SUBMIT_BUTTON => $helper->__('Submit button')
            );
        }
        
        return  $this->_options;
    }

}