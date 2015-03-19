<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Kwartaalbijdrage_Form_Settings extends CRM_Core_Form {
    function buildQuickForm() {

        $this->add('text', 'basisbedrag', t('Basisbedrag'), true);
        $this->add('text', 'ledenvergoeding', t('Ledenvergoeding'), true);
        $this->add('text', 'tribunevergoeding', t('Vergoeding'), true);

        $this->addFormRule(array('CRM_Kwartaalbijdrage_Form_Settings', 'checkMoney'));

        $this->addButtons(array(
            array(
                'type' => 'submit',
                'name' => ts('Submit'),
                'isDefault' => TRUE,
            ),
        ));

        parent::buildQuickForm();
    }

    public static function checkMoney($fields) {
        $basisbedrag = self::convertToFloat($fields['basisbedrag']);
        $ledenvergoeding = self::convertToFloat($fields['ledenvergoeding']);
        $tribunevergoeding = self::convertToFloat($fields['tribunevergoeding']);

        $errors = array();
        if (!is_numeric($basisbedrag)) {
            $errors['basisbedrag'] = ts('Enter a valid amount');
        }
        if (!is_numeric($ledenvergoeding)) {
            $errors['ledenvergoeding'] = ts('Enter a valid amount');
        }
        if (!is_numeric($tribunevergoeding)) {
            $errors['tribunevergoeding'] = ts('Enter a valid amount');
        }
        if (count($errors)) {
            return $errors;
        }

        return true;
    }

    function setDefaultValues() {
        $setting = CRM_Kwartaalbijdrage_Settings::singleton();
        $defaults['basisbedrag'] =  CRM_Utils_Money::format($setting->getBasisbedragPerJaar(), 'EUR');
        $defaults['ledenvergoeding'] =  CRM_Utils_Money::format($setting->getLedenvergoedingPerLid(), 'EUR');
        $defaults['tribunevergoeding'] =  CRM_Utils_Money::format($setting->getTribunevergoedingPerTribune(), 'EUR');
        return $defaults;
    }

    function postProcess() {
        $values = $this->exportValues();

        $basisbedrag = self::convertToFloat($values['basisbedrag']);
        $ledenvergoeding = self::convertToFloat($values['ledenvergoeding']);
        $tribunevergoeding = self::convertToFloat($values['tribunevergoeding']);

        CRM_Kwartaalbijdrage_Settings::save($basisbedrag, $ledenvergoeding, $tribunevergoeding);

        parent::postProcess();
    }

    static function convertToFloat($money) {
        $config = CRM_Core_Config::singleton();
        $currency = $config->defaultCurrency;

        $_currencySymbols = CRM_Core_PseudoConstant::get('CRM_Contribute_DAO_Contribution', 'currency', array('keyColumn' => 'name', 'labelColumn' => 'symbol'));
        $currencySymbol = CRM_Utils_Array::value($currency, $_currencySymbols, $currency);

        $replacements = array(
            $currency => '',
            $currencySymbol => '',
            $config->monetaryThousandSeparator => '',
        );
        $return =  trim(strtr($money, $replacements));
        $decReplacements = array(
            $config->monetaryDecimalPoint => '.',
        );
        $return = trim(strtr($return, $decReplacements));
        return $return;
    }
}
