<?php
// $Id$

/**
 * Rules editing page callback.
 *
 * Simple wrapper for the form, only required to add the JavaScript.
 */
function ding_campaign_admin_rule_page($node) {
  // Use the admin module's theme if available.
  if (function_exists('_admin_init_theme')) {
    _admin_init_theme();
  }
  drupal_add_js(drupal_get_path('module', 'ding_campaign') . '/js/ding_campaign.rules.js');
  drupal_add_js(array('dingCampaignRules' => array(
    'autocompleteUrl' => url('campaign_rules/autocomplete/'),
  )), 'setting');

  return drupal_get_form('ding_campaign_admin_rule_form', $node);
}

/**
 * Admin interface for campaign display rules.
 */
function ding_campaign_admin_rule_form($form_state, $node) {
  $form = array();

  $form['nid'] = array(
    '#type' => 'value',
    '#value' => $node->nid,
  );

  $form['rules'] = array(
    '#type' => 'fieldset',
    '#title' => t('Rules'),
    '#description' => t('The more rules that match on a page, the higher rank the campaign will have. The "generic" rule will give a small bit of rank on all pages, so that the campaign will show up if there is not enough relevant campaigns.'),
    '#attributes' => array('id' => 'campaign-rules'),
    '#tree' => TRUE,
  );

  if (isset($node->campaign_rules) && !empty($node->campaign_rules)) {
    foreach ($node->campaign_rules as $delta => $rule) {
      $form['rules'][$delta] = array(
        '#type' => 'ding_campaign_rule',
        '#value' => $rule,
        '#nid' => $node->nid,
      );
    }
  }
  else {
    $form['rules'][0] = array(
      '#type' => 'ding_campaign_rule',
      '#nid' => $node->nid,
    );
  }

  $form['new_rule_button'] = array(
    '#type' => 'button',
    '#value' => 'Add another',
    '#ahah' => array(
      'path' => 'node/' . $node->nid . '/campaign_rules/ahah',
      'wrapper' => 'campaign-rules',
      'method' => 'append',
      'effect' => 'slide'
    ),
  );

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save changes'),
  );

  return $form;
}

/**
 * Validation for campaign display rules form.
 */
function ding_campaign_admin_rule_form_validate($form, &$form_state) {
  foreach ($form_state['values']['rules'] as $delta => $rule) {
    $value = trim($rule['value']);
    if ($rule['type'] == 'generic') {
      // Generic fields have no value, only their type.
      $form_state['values']['rules'][$delta]['value'] = NULL;
    }
    elseif (empty($value)) {
      // Remove the empty rules, so they won't get saved to the database.
      unset($form_state['values']['rules'][$delta]);
    }
    elseif ($rule['type'] == 'search_term') {
      // Force lowercase terms (we do the same when searching,
      // so searching is effectively case-insensitive).
      $form_state['values']['rules'][$delta]['value'] = drupal_strtolower($value);
    }
    elseif ($rule['type'] == 'page' || $rule['type'] == 'library' || $rule['type'] == 'taxonomy') {
      $matches = array();
      // Match [nid:54], [tid:4], etc. from the text string.
      if (preg_match('/\[[tn]id:(\d+)\]/', $value, $matches)) {
        // Store the matched ID.
        $form_state['values']['rules'][$delta]['value_id'] = $matches[1];
      }
      elseif (!is_numeric($value) || $value < 0) {
        form_error($form['rules'][$delta], t('%value is not a valid value for the selected type (%type).', array('%value' => $rule['value'], '%type' => $rule['type'])));
      }
    }
  }
}

/**
 * Submit handler for campaign display rules form.
 */
function ding_campaign_admin_rule_form_submit($form, &$form_state) {
  // First, remove the existing rules
  db_query("DELETE FROM {ding_campaign_rule} WHERE nid = %d", $form_state['values']['nid']);

  // Then write the new set of rules
  // Use array_values to make sure that we have no missing numbers in the
  // delta, and use the new key to overwrite the delta setting on the rule.
  foreach (array_values($form_state['values']['rules']) as $delta => $rule) {
    $rule['delta'] = $delta;
    drupal_write_record('ding_campaign_rule', $rule);
  } 
}

/*
  This function is largely based on the poll module, its been simplified for reuse.
  $fields is the specific form elements you want to attach via ahah,
  $name is the form fields array key... e.g. the name for $form['title'] is "title"
*/ 
function ding_campaign_admin_rule_form_ahah($form_state, $node) {
  // Use the admin module's theme if available.
  if (function_exists('_admin_init_theme')) {
    _admin_init_theme();
  }
  $form_state = array('submitted' => FALSE);
  $form_build_id = $_POST['form_build_id'];
  // Add the new element to the stored form. Without adding the element to the
  // form, Drupal is not aware of this new elements existence and will not
  // process it. We retreive the cached form, add the element, and resave.
  $form = form_get_cache($form_build_id, $form_state);

  // Delta is assigned to the count of current elements, so it'll be the 
  // next in the zero-based list.
  $delta = count(element_children($form['rules']));

  $form['rules'][$delta] = array(
    '#type' => 'ding_campaign_rule',
    '#delta' => $delta,
    '#nid' => $node->nid,
  );
  form_set_cache($form_build_id, $form, $form_state);
  $form += array(
    '#post' => $_POST,
    '#programmed' => FALSE,
  );
  // Rebuild the form.
  $form = form_builder($_POST['form_id'], $form, $form_state);
  // Render and return the new field.
  drupal_json(array('data' => drupal_render($form['rules'][$delta]), 'status' => TRUE));
  exit();
}

/**
 * Autocomplete callback for rule values.
 *
 * @param string $type
 *    The type of thing we're searching for.
 * @param string $term
 *    The search term.
 */
function ding_campaign_admin_autocomplete($type, $term) {
  $matches = array();

  if ($type == 'page') {
    $query = db_query("SELECT nid, title FROM node WHERE title LIKE('%%%s%%') LIMIT 5;", $term);
    while ($row = db_fetch_array($query)) {
      $value = _ding_campaign_strip_title($row['title']) . ' [nid:' . $row['nid'] . ']';
      $matches[$value] = $value;
    }
  }
  elseif ($type == 'library') {
    $query = db_query("SELECT nid, title FROM node WHERE type = 'library' AND title LIKE('%%%s%%') LIMIT 5;", $term);
    while ($row = db_fetch_array($query)) {
      $value = _ding_campaign_strip_title($row['title']) . ' [nid:' . $row['nid'] . ']';
      $matches[$value] = $value;
    }
  }
  elseif ($type == 'taxonomy') {
    $query = db_query("SELECT tid, name FROM term_data WHERE name LIKE('%%%s%%') LIMIT 5;", $term);
    while ($row = db_fetch_array($query)) {
      $value = _ding_campaign_strip_title($row['name']) . ' [tid:' . $row['tid'] . ']';
      $matches[$value] = $value;
    }
  }
  drupal_json($matches);
  exit();
}

/**
 * Helper function to strip unhealthy characters form titles.
 */
function _ding_campaign_strip_title($title) {
  return str_replace(array('"', "'", ',', '[', ']', '&', '<', '>'), '', check_plain($title));
}

