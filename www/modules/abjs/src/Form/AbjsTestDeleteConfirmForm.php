<?php

namespace Drupal\abjs\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class AbjsTestDeleteConfirmForm extends ConfirmFormBase {
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
    return 'abjs_test_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Do you want to delete test %id?', array('%id' => $this->id));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
      return new Url('abjs.test_admin');
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
   * @param int $tid
   *   The ID of the item to be deleted.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $tid = NULL) {
    $this->id = $tid;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    db_delete('abjs_test')
      ->condition('tid', $this->id)
      ->execute();
    db_delete('abjs_test_condition')
      ->condition('tid', $this->id)
      ->execute();
    db_delete('abjs_test_experience')
      ->condition('tid', $this->id)
      ->execute();

    drupal_set_message(t('Test %id has been deleted.', ['%id' => $this->id]));

    $form_state->setRedirect('abjs.test_admin');
  }

}
