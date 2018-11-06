<?php

namespace Drupal\abjs\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class AbjsExperienceDeleteConfirmForm extends ConfirmFormBase {
  /**
   * The ID of the item to delete.
   *
   * @var string
   */
  protected $id;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'abjs_experience_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Do you want to delete experience %id?', array('%id' => $this->id));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
      return new Url('abjs.experience_admin');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return t('This action cannot be undone.');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelText() {
    return t('Cancel');
  }

  /**
   * {@inheritdoc}
   *
   * @param int $eid
   *   The ID of the item to be deleted.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $eid = NULL) {
    $this->id = $eid;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    db_delete('abjs_experience')
      ->condition('eid', $this->id)
      ->execute();
    db_delete('abjs_test_experience')
      ->condition('eid', $this->id)
      ->execute();

    drupal_set_message(t('Experience %id has been deleted.', ['%id' => $this->id]));

    $form_state->setRedirect('abjs.experience_admin');
  }

}
