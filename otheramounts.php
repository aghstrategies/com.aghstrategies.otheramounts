<?php

require_once 'otheramounts.civix.php';
use CRM_Otheramounts_ExtensionUtil as E;


/**
 * Implements hook_civicrm_validateForm().
 */
function otheramounts_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  // IF on a contribution or event registration form
  if ($formName == 'CRM_Contribute_Form_Contribution_Main' || $formName == 'CRM_Event_Form_Registration_Register') {
    $otherPriceOption = NULL;
    $otherPriceField = NULL;
    foreach ($form->_priceSet['fields'] as $fieldId => $fieldDetails) {
      // With an other amount option configured
      if (in_array($fieldId, Civi::settings()->get('otheramount_pricefields'))) {
        $otherPriceField = $fieldId;
        foreach ($fieldDetails['options'] as $key => $values) {
          // TODO: handle this differently than looking for the label
          if (substr($values['label'], 0, 8) === 'Pay What') {
            $otherPriceOption = $key;
          }
        }
      }
    }
    // If user has selected the other price option ensure they have entered a value greater than 20
    if ($otherPriceOption != NULL && $otherPriceField != NULL) {
      if ($form->_submitValues["price_{$otherPriceField}"] == $otherPriceOption && $form->_submitValues["other_amount_{$otherPriceField}"] < 20) {
        $errors["other_amount_$otherPriceField"] = ts('Other Amount must be greater than $20.00');
      }
    }
  }
}

/**
 * Implements hook_civicrm_buildform().
 */
function otheramounts_civicrm_buildform($formName, &$form) {
  switch ($formName) {
    case 'CRM_Price_Form_Field':
      $form->add('checkbox', 'otheramount', ts('Allow Other Amounts'));
      CRM_Core_Resources::singleton()->addScriptFile('com.aghstrategies.otheramounts', 'js/priceFieldSettings.js');
      //set default value
      $defaults = array('otheramount' => 0);
      if (in_array($form->getVar('_fid'), Civi::settings()->get('otheramount_pricefields'))) {
        $defaults['otheramount'] = 1;
      }
      $form->setDefaults($defaults);
      // Assumes templates are in a templates folder relative to this file.
      $templatePath = realpath(dirname(__FILE__) . "/templates");
      CRM_Core_Region::instance('form-body')->add(array(
        'template' => "{$templatePath}/otherAmounts.tpl",
      ));
      break;

    case 'CRM_Contribute_Form_Contribution_Main':
    case 'CRM_Event_Form_Registration_Register':
      $otherAmountFields = [];
      $detsForJs = [];
      $templatePath = realpath(dirname(__FILE__) . "/templates");
      foreach ($form->_priceSet['fields'] as $fieldId => $fieldDetails) {
        if (in_array($fieldId, Civi::settings()->get('otheramount_pricefields'))) {
          $otherAmounts = TRUE;
          foreach ($fieldDetails['options'] as $key => $values) {
            // TODO: handle this differently than looking for the label
            if (substr($values['label'], 0, 8) === 'Pay What') {
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
      break;
  }
}

/**
 * Implements hook_civicrm_buildAmount().
 */
function otheramounts_civicrm_buildAmount($pageType, &$form, &$amount) {
  $fieldsToAddOtherAmountOptionFor = Civi::settings()->get('otheramount_pricefields');
  if (isset($form->_priceSet['fields'])) {
    foreach ($form->_priceSet['fields'] as $fieldId => $fieldDetails) {
      if (in_array($fieldId, $fieldsToAddOtherAmountOptionFor) && !empty($form->_submitValues["other_amount_$fieldId"])) {
        $otherAmounts = TRUE;
        foreach ($fieldDetails['options'] as $optionId => $values) {
          if (substr($values['label'], 0, 8) === 'Pay What') {
            $amount[$fieldId]['options'][$optionId]['amount'] = $form->_submitValues["other_amount_$fieldId"];
          }
        }
      }
    }
  }
}

/**
 * Implements hook_civicrm_postProcess().
 */
function otheramounts_civicrm_postProcess($formName, &$form) {
  if ($formName == 'CRM_Price_Form_Field') {
    $fieldsToAddOtherAmountOptionFor = Civi::settings()->get('otheramount_pricefields');
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
    Civi::settings()->set('otheramount_pricefields', $fieldsToAddOtherAmountOptionFor);
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
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function otheramounts_civicrm_xmlMenu(&$files) {
  _otheramounts_civix_civicrm_xmlMenu($files);
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
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function otheramounts_civicrm_postInstall() {
  _otheramounts_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function otheramounts_civicrm_uninstall() {
  _otheramounts_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function otheramounts_civicrm_enable() {
  _otheramounts_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function otheramounts_civicrm_disable() {
  _otheramounts_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function otheramounts_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _otheramounts_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function otheramounts_civicrm_managed(&$entities) {
  _otheramounts_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function otheramounts_civicrm_caseTypes(&$caseTypes) {
  _otheramounts_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function otheramounts_civicrm_angularModules(&$angularModules) {
  _otheramounts_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function otheramounts_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _otheramounts_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function otheramounts_civicrm_entityTypes(&$entityTypes) {
  _otheramounts_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 *
function otheramounts_civicrm_preProcess($formName, &$form) {

} // */

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
