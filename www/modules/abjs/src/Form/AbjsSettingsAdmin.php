<?php

namespace Drupal\abjs\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class AbjsSettingsAdmin extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'abjs_settings_admin';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['abjs.settings'];
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('abjs.settings');
    // Each applicable test will have one cookie. The cookie prefix will prefix
    // the name of all test cookies.
    $form['cookie_prefix'] = [
      '#type' => 'textfield',
      '#title' => t('Cookie Prefix'),
      '#default_value' => $config->get('cookie.prefix'),
      '#description' => t('This string will prefix all A/B test cookie names'),
      '#size' => 10,
      '#maxlength' => 10,
    ];
    $form['cookie_lifetime'] = [
      '#type' => 'textfield',
      '#title' => t('Cookie Lifetime'),
      '#description' => t('Enter cookie lifetime in days'),
      '#default_value' => $config->get('cookie.lifetime'),
      '#size' => 4,
      '#maxlength' => 10,
    ];
    $form['cookie_domain'] = [
      '#type' => 'textfield',
      '#title' => t('Cookie Domain'),
      '#description' => t('Enter the domain to which the test cookies will be set, e.g. example.com. Leave blank to set the cookies to the domain of the page where the tests are occurring.'),
      '#default_value' => $config->get('cookie.domain'),
      '#size' => 50,
      '#maxlength' => 100,
    ];
    $form['cookie_secure'] = [
      '#type' => 'select',
      '#title' => t('Use Secure Cookies?'),
      '#description' => t('This sets the secure flag on A/B test cookies'),
      '#options' => [
        0 => t('No'),
        1 => t('Yes'),
      ],
      '#default_value' => $config->get('cookie.secure'),
    ];
    $form['ace'] = [
      '#type' => 'select',
      '#title' => t('Use Ace Code Editor?'),
      '#description' => t('Use Ace Code Editor for entering Condition and Experience scripts. If chosen, it will be loaded via https://cdnjs.cloudflare.com.'),
      '#options' => [
        0 => t('No'),
        1 => t('Yes'),
      ],
      '#default_value' => $config->get('ace'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('abjs.settings')
      ->set('cookie.prefix', $form_state->getValue('cookie_prefix'))
      ->set('cookie.lifetime', $form_state->getValue('cookie_lifetime'))
      ->set('cookie.domain', $form_state->getValue('cookie_domain'))
      ->set('cookie.secure', $form_state->getValue('cookie_secure'))
      ->set('ace', $form_state->getValue('ace'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
