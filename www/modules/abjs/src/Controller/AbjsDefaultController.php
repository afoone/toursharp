<?php

/**
 * @file
 * Contains \Drupal\abjs\Controller\DefaultController.
 */

namespace Drupal\abjs\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Drupal\Core\Database\Database;
use Drupal\Core\Url;
/**
 * Default controller for the abjs module.
 */
class AbjsDefaultController extends ControllerBase {

  /**
   * Lists all tests in a default table theme.
   *
   * Sorted by active tests first, then modified most recently, then created most
   * recently. For each test, link to test, and list active status, conditions
   * applied (with links), experiences with fractions assigned (with links),
   * and created and edited info.
   */
  public function abjs_test_admin() {
    $db = Database::getConnection();
    $renderer = \Drupal::service('renderer');
    $date_Formatter = \Drupal::service('date.formatter');

    $output = [];
    $header = [
      t('ID'),
      t('Name'),
      t('Status'),
      t('Conditions'),
      t('Experiences'),
      t('Created'),
      t('Created By'),
      t('Changed'),
      t('Changed By'),
    ];
    $rows = [];
    $active_array = [t('Inactive'), t('Active')];
    $tests = $db->query("SELECT * FROM {abjs_test} ORDER BY active DESC, changed DESC, created DESC")->fetchAll();
    foreach ($tests as $test) {
      $test_link = [
        '#title' => $test->name,
        '#type' => 'link',
        '#url' => Url::fromRoute('abjs.test_edit_form', ['tid' => $test->tid])
      ];

      $condition_list = [];
      $condition_output = '';
      $conditions = $db->query("SELECT tc.cid, c.name FROM {abjs_test_condition} AS tc INNER JOIN {abjs_condition} AS c ON tc.cid = c.cid WHERE tc.tid = :tid", [':tid' => $test->tid])->fetchAll();
      if (!empty($conditions)) {
        foreach ($conditions as $condition) {
          $condition_link = [
            '#title' => $condition->name . ' (c_' . $condition->cid . ')',
            '#type' => 'link',
            '#url' => Url::fromRoute('abjs.condition_edit_form', ['cid' => $condition->cid])
          ];
          $condition_list[] = $renderer->render($condition_link);
        }
        $condition_output = [
          '#theme' => 'item_list',
          '#items' => $condition_list,
        ];
      }

      $experience_list = [];
      $experience_output = '';
      $experiences = $db->query("SELECT te.eid, te.fraction, e.name FROM {abjs_test_experience} AS te INNER JOIN {abjs_experience} AS e ON te.eid = e.eid WHERE te.tid = :tid", [':tid' => $test->tid])->fetchAll();
      if (!empty($experiences)) {
        foreach ($experiences as $experience) {
          $experience_link = [
            '#title' => '[' . $experience->fraction . '] ' . $experience->name . ' (e_' . $experience->eid . ')',
            '#type' => 'link',
            '#url' => Url::fromRoute('abjs.experience_edit_form', ['eid' => $experience->eid])
          ];
          $experience_list[] = $renderer->render($experience_link);
        }
        $experience_output = [
          '#theme' => 'item_list',
          '#items' => $experience_list,
        ];
      }
      $user_created = User::load($test->created_by);
      $user_changed = User::load($test->changed_by);
      $rows[] = array(
             't_' . $test->tid,
             $renderer->render($test_link),
             $active_array[$test->active],
             $renderer->render($condition_output),
             $renderer->render($experience_output),
             $date_Formatter->format($test->created),
             $user_created->toLink(),
             $date_Formatter->format($test->changed),
             $user_changed->toLink(),
           );

    }

    $output['add'] = [
      '#title' => t('Add new test'),
      '#type' => 'link',
      '#url' => Url::fromRoute('abjs.test_add_form')
    ];
    $output['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];
    return $output;
  }

  /**
   * Lists all conditions in default table, sorted by modified date.
   *
   * For each condition, link to edit form, and list created and edited info.
   */
  public function abjs_condition_admin() {
    $db = Database::getConnection();
    $renderer = \Drupal::service('renderer');
    $date_Formatter = \Drupal::service('date.formatter');

    $output = [];
    $header = [
      t('ID'),
      t('Name'),
      t('Created'),
      t('Created By'),
      t('Changed'),
      t('Changed By'),
    ];
    $rows = [];

    $conditions = $db->query("SELECT * FROM {abjs_condition} ORDER BY changed DESC, created DESC")->fetchAll();
    foreach ($conditions as $condition) {
      $condition_link = [
        '#title' => $condition->name,
        '#type' => 'link',
        '#url' => Url::fromRoute('abjs.condition_edit_form', ['cid' => $condition->cid])
      ];
      $user_created = User::load($condition->created_by);
      $user_changed = User::load($condition->changed_by);

      $rows[] = [
           'c_' . $condition->cid,
           $renderer->render($condition_link),
           $date_Formatter->format($condition->created),
           $user_created->toLink(),
           $date_Formatter->format($condition->changed),
           $user_changed->toLink(),
      ];
    }
    $output['add'] = [
      '#title' => t('Add new condition'),
      '#type' => 'link',
      '#url' => Url::fromRoute('abjs.condition_add_form')
    ];
    $output['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];
    return $output;

  }

  /**
   * Lists all experiences in default table, sorted by modified date.
   *
   * For each experience, link to edit form, and list created and edited info.
   */
  public function abjs_experience_admin() {
    $db = Database::getConnection();
    $renderer = \Drupal::service('renderer');
    $date_Formatter = \Drupal::service('date.formatter');

    $output = [];
    $header = [
      t('ID'),
      t('Name'),
      t('Created'),
      t('Created By'),
      t('Changed'),
      t('Changed By'),
    ];
    $rows = [];

    $experiences = $db->query("SELECT * FROM {abjs_experience} ORDER BY changed DESC, created DESC")->fetchAll();
    foreach ($experiences as $experience) {
      $experience_link = [
        '#title' => $experience->name,
        '#type' => 'link',
        '#url' => Url::fromRoute('abjs.experience_edit_form', ['eid' => $experience->eid])
      ];
      $user_created = User::load($experience->created_by);
      $user_changed = User::load($experience->changed_by);

      $rows[] = [
           'e_' . $experience->eid,
           $renderer->render($experience_link),
           $date_Formatter->format($experience->created),
           $user_created->toLink(),
           $date_Formatter->format($experience->changed),
           $user_changed->toLink(),
      ];
    }
    $output['add'] = [
      '#title' => t('Add new experience'),
      '#type' => 'link',
      '#url' => Url::fromRoute('abjs.experience_add_form')
    ];
    $output['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];
    return $output;

  }

}
