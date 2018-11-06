<?php

namespace Drupal\abjs\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class AbjsExperienceForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'abjs_experience';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $eid = NULL) {
    $form = array();
    $experience_name_default = "";
    $experience_script_default = "";
    if (!empty($eid)) {
      $experience_result = db_query('SELECT name, script FROM {abjs_experience} WHERE eid = :eid', array(':eid' => $eid));
      $experience = $experience_result->fetchObject();
      if (empty($experience)) {
        drupal_set_message(t('The requested experience does not exist.'), 'error');
        return $form;
      }
      $experience_name_default = $experience->name;
      $experience_script_default = $experience->script;
      $form['eid'] = array('#type' => 'value', '#value' => $eid);
    }

    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => t('Experience Name'),
      '#default_value' => $experience_name_default,
      '#size' => 30,
      '#maxlength' => 50,
      '#required' => TRUE,
    );

    $form['script'] = array(
      '#type' => 'textarea',
      '#title' => t('Experience Script'),
      '#default_value' => $experience_script_default,
      '#description' => t('Any valid javascript to load in head. Leave empty for a Control. Read the documentation for more examples.'),
      '#rows' => 3,
    );

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['save'] = array(
      '#type' => 'submit',
      '#value' => t('Save'),
      '#weight' => 5,
      '#submit' => array('::saveExperience'),
    );
    $form['actions']['cancel'] = array(
      '#type' => 'submit',
      '#value' => t('Cancel'),
      '#weight' => 10,
      '#submit' => array('::cancelExperience'),
      '#limit_validation_errors' => array(),
    );
    if (!empty($eid)) {
      $form['actions']['delete'] = array(
        '#type' => 'submit',
        '#value' => t('Delete'),
        '#weight' => 15,
        '#submit' => array('::deleteExperience'),
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

  public function saveExperience(array &$form, FormStateInterface $form_state){
    $user = \Drupal::currentUser();
    if ($form_state->hasValue('eid')) {
      // This is a modified experience, so use update.
      db_update('abjs_experience')
        ->fields(array(
          'name' => $form_state->getValue('name'),
          'script' => $form_state->getValue('script'),
          'changed' => REQUEST_TIME,
          'changed_by' => $user->id(),
        ))
        ->condition('eid', $form_state->getValue('eid'), '=')
        ->execute();
      drupal_set_message(t("Successfully updated experience"));

    }
    else {
      // This is a new experience, so use insert.
      db_insert('abjs_experience')
        ->fields(array(
          'name' => $form_state->getValue('name'),
          'script' => $form_state->getValue('script'),
          'created' => REQUEST_TIME,
          'created_by' => $user->id(),
          'changed' => REQUEST_TIME,
          'changed_by' => $user->id(),
        ))->execute();
      drupal_set_message(t("Successfully saved new experience"));

    }
    $form_state->setRedirect('abjs.experience_admin');
  }

  public function cancelExperience(array &$form, FormStateInterface $form_state){
    $form_state->setRedirect('abjs.experience_admin');
  }

  public function deleteExperience(array &$form, FormStateInterface $form_state){
    $form_state->setRedirect('abjs.experience_delete_confirm_form', array('eid' => $form_state->getValue('eid')));
  }

}
