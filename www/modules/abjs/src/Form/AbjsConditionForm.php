<?php

namespace Drupal\abjs\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class AbjsConditionForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'abjs_condition';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $cid = NULL) {
    $form = array();
    $condition_name_default = "";
    $condition_script_default = "";
    if (!empty($cid)) {
      $condition_result = db_query('SELECT name, script FROM {abjs_condition} WHERE cid = :cid', array(':cid' => $cid));
      $condition = $condition_result->fetchObject();
      if (empty($condition)) {
        drupal_set_message(t('The requested condition does not exist.'), 'error');
        return $form;
      }
      $condition_name_default = $condition->name;
      $condition_script_default = $condition->script;
      $form['cid'] = array('#type' => 'value', '#value' => $cid);
    }

    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => t('Condition Name'),
      '#default_value' => $condition_name_default,
      '#size' => 30,
      '#maxlength' => 50,
      '#required' => TRUE,
    );

    $form['script'] = array(
      '#type' => 'textarea',
      '#title' => t('Condition Script'),
      '#default_value' => $condition_script_default,
      '#description' => t('Any valid javascript with a return statement at the end, returning true or false. Read the documentation for examples'),
      '#rows' => 3,
      '#required' => TRUE,
    );
    $form['actions'] = array('#type' => 'actions');
    $form['actions']['save'] = array(
      '#type' => 'submit',
      '#value' => t('Save'),
      '#weight' => 5,
      '#submit' => array('::saveCondition'),
    );
    $form['actions']['cancel'] = array(
      '#type' => 'submit',
      '#value' => t('Cancel'),
      '#weight' => 10,
      '#submit' => array('::cancelCondition'),
      '#limit_validation_errors' => array(),
    );
    if (!empty($cid)) {
      $form['actions']['delete'] = array(
        '#type' => 'submit',
        '#value' => t('Delete'),
        '#weight' => 15,
        '#submit' => array('::deleteCondition'),
      );
    }

    // Add ace code editor for syntax highlighting on the script field.
    if (\Drupal::config('abjs.settings')->get('ace') == 1) {
      $form['#attached']['library'][] = 'abjs/ace-editor';
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

  public function saveCondition(array &$form, FormStateInterface $form_state){
    $user = \Drupal::currentUser();
    if ($form_state->hasValue('cid')) {
      // This is a modified condition, so use update.
      db_update('abjs_condition')
        ->fields(array(
          'name' => $form_state->getValue('name'),
          'script' => $form_state->getValue('script'),
          'changed' => REQUEST_TIME,
          'changed_by' => $user->id(),
        ))
        ->condition('cid', $form_state->getValue('cid'), '=')
        ->execute();
      drupal_set_message(t("Successfully updated condition"));

    }
    else {
      // This is a new condition, so use insert.
      db_insert('abjs_condition')
        ->fields(array(
          'name' => $form_state->getValue('name'),
          'script' => $form_state->getValue('script'),
          'created' => REQUEST_TIME,
          'created_by' => $user->id(),
          'changed' => REQUEST_TIME,
          'changed_by' => $user->id(),
        ))->execute();
      drupal_set_message(t("Successfully saved new condition"));

    }
    $form_state->setRedirect('abjs.condition_admin');
  }

  public function cancelCondition(array &$form, FormStateInterface $form_state){
    $form_state->setRedirect('abjs.condition_admin');
  }

  public function deleteCondition(array &$form, FormStateInterface $form_state){
    $form_state->setRedirect('abjs.condition_delete_confirm_form', array('cid' => $form_state->getValue('cid')));
  }

}
