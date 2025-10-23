<?php

require_once 'otheramounts.civix.php';
use CRM_Otheramounts_ExtensionUtil as E;

/**
 * Implements hook_civicrm_buildform().
 */
function otheramounts_civicrm_buildform($formName, &$form) {
  if ($formName == 'CRM_Price_Form_Field' || $formName == 'CRM_Contribute_Form_Contribution_Main') {
    $fieldsToAddOtherAmountOptionFor = otheramounts_getsetting();
    // Settings Form
    if ($formName == 'CRM_Price_Form_Field') {
      $form->add('checkbox', 'otheramount', ts('Allow Other Amounts'));
      CRM_Core_Resources::singleton()->addScriptFile('com.aghstrategies.otheramounts', 'js/priceFieldSettings.js');
      //set default value
      $defaults = array('otheramount' => 0);
      $fieldsToAddOtherAmountOptionFor = otheramounts_getsetting();
      if (in_array($form->getVar('_fid'), $fieldsToAddOtherAmountOptionFor)) {
        $defaults['otheramount'] = 1;
      }
      $form->setDefaults($defaults);
      // Assumes templates are in a templates folder relative to this file.
      $templatePath = realpath(dirname(__FILE__) . "/templates");
      CRM_Core_Region::instance('form-body')->add(array(
        'template' => "{$templatePath}/otherAmounts.tpl",
      ));
    }

    // Contribution Form
    if ($formName == 'CRM_Contribute_Form_Contribution_Main') {
      $otherAmountFields = [];
      $detsForJs = [];
      $templatePath = realpath(dirname(__FILE__) . "/templates");
      foreach ($form->_priceSet['fields'] as $fieldId => $fieldDetails) {
        if (in_array($fieldId, $fieldsToAddOtherAmountOptionFor)) {
          $otherAmounts = TRUE;
          foreach ($fieldDetails['options'] as $key => $values) {
            if ($values['label'] == 'Other Amount') {
              $detsForJs[$fieldId] = $key;
            }
          }
          $otherAmountFieldName = "other_amount_$fieldId";
          $form->add('text', $otherAmountFieldName, ts('Other Amount'));
          $form->addRule($otherAmountFieldName, ts('Please enter a number'), 'numeric');
          $otherAmountFields[] = $otherAmountFieldName;
        }
      }
      if (!empty($otherAmountFields)) {
        CRM_Core_Region::instance('form-body')->add(array(
          'template' => "{$templatePath}/contribForm.tpl",
        ));
        $form->assign('otherAmounts', $otherAmountFields);
        CRM_Core_Resources::singleton()->addVars('otheramounts', array('otherFields' => $detsForJs));
        CRM_Core_Resources::singleton()->addScriptFile('com.aghstrategies.otheramounts', 'js/otherAmount.js');
      }
    }
  }
}

/**
 * Implements hook_civicrm_buildAmount().
 */
function otheramounts_civicrm_buildAmount($pageType, &$form, &$amount) {
  $fieldsToAddOtherAmountOptionFor = otheramounts_getsetting();
  if (isset($form->_priceSet['fields'])) {
    foreach ($form->_priceSet['fields'] as $fieldId => $fieldDetails) {
      if (in_array($fieldId, $fieldsToAddOtherAmountOptionFor) && !empty($form->_submitValues["other_amount_$fieldId"])) {
        $otherAmounts = TRUE;
        foreach ($fieldDetails['options'] as $optionId => $values) {
          if ($values['label'] == 'Other Amount') {
            $amount[$fieldId]['options'][$optionId]['amount'] = $form->_submitValues["other_amount_$fieldId"];
          }
        }
      }
    }
  }
}

function otheramounts_getsetting() {
  $fieldsToAddOtherAmountOptionFor = [];
  try {
    $otherFields = civicrm_api3('Setting', 'get', array(
      'return' => "otheramount_pricefields",
    ));
  }
  catch (CRM_Core_Exception $e) {
    $error = $e->getMessage();
    CRM_Core_Error::debug_log_message(ts('API Error %1', array(
      'domain' => 'com.aghstrategies.otheramounts',
      1 => $error,
    )));
  }
  if (!empty($otherFields['values'][1]['otheramount_pricefields'])) {
    $fieldsToAddOtherAmountOptionFor = $otherFields['values'][1]['otheramount_pricefields'];
  }
  return $fieldsToAddOtherAmountOptionFor;
}

/**
 * Implements hook_civicrm_postProcess().
 */
function otheramounts_civicrm_postProcess($formName, &$form) {
  if ($formName == 'CRM_Price_Form_Field') {
    $fieldsToAddOtherAmountOptionFor = otheramounts_getsetting();
    $fid = $form->getVar('_fid');
    if ($form->_submitValues['otheramount'] == 1) {
      if (in_array($fid, $fieldsToAddOtherAmountOptionFor)) {
        return;
      }
      else {
        $fieldsToAddOtherAmountOptionFor[] = $fid;
      }
    }
    else {
      if (in_array($fid, $fieldsToAddOtherAmountOptionFor)) {
        $key = array_search($fid, $fieldsToAddOtherAmountOptionFor);
        unset($fieldsToAddOtherAmountOptionFor[$key]);
      }
    }
    try {
      $result = civicrm_api3('Setting', 'create', array(
        'otheramount_pricefields' => $fieldsToAddOtherAmountOptionFor,
      ));
    }
    catch (CRM_Core_Exception $e) {
      $error = $e->getMessage();
      CRM_Core_Error::debug_log_message(ts('API Error %1', array(
        'domain' => 'com.aghstrategies.otheramounts',
        1 => $error,
      )));
    }
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function otheramounts_civicrm_config(&$config) {
  _otheramounts_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function otheramounts_civicrm_install() {
  _otheramounts_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function otheramounts_civicrm_enable() {
  _otheramounts_civix_civicrm_enable();
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 *

 // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 *
function otheramounts_civicrm_navigationMenu(&$menu) {
  _otheramounts_civix_insert_navigation_menu($menu, 'Mailings', array(
    'label' => E::ts('New subliminal message'),
    'name' => 'mailing_subliminal_message',
    'url' => 'civicrm/mailing/subliminal',
    'permission' => 'access CiviMail',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _otheramounts_civix_navigationMenu($menu);
} // */
