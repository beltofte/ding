<?php
// $Id: string.inc,v 1.3.2.2 2009/10/09 22:35:56 merlinofchaos Exp $

/**
 * @file
 *
 * Plugin to provide an argument handler for a raw string
 */

/**
 * Implementation of specially named hook_ctools_arguments().
 */
function ctools_string_ctools_arguments() {
  return array(
    'title' => t("String"),
    // keyword to use for %substitution
    'keyword' => 'string',
    'description' => t('A string is a minimal context that simply holds a string that can be used for some other purpose.'),
    'settings form' => 'ctools_string_settings_form',
    'context' => 'ctools_string_context',
    'placeholder form' => array(
      '#type' => 'textfield',
      '#description' => t('Enter a value for this argument'),
    ),
    'path placeholder' => 'ctools_string_path_placeholder', // This is in pagemanager.
  );
}

/**
 * Discover if this argument gives us the term we crave.
 */
function ctools_string_context($arg = NULL, $conf = NULL, $empty = FALSE) {
  // If unset it wants a generic, unfilled context.
  if ($empty) {
    return ctools_context_create_empty('string');
  }

  $context = ctools_context_create('string', $arg);
  $context->original_argument = $arg;

  return $context;
}

/**
 * Settings form for the argument
 */
function ctools_string_settings_form(&$form, &$form_state, $conf) {
  $form['settings']['use_tail'] = array(
    '#title' => t('Get all arguments after this one'),
    '#type' => 'checkbox',
    '#default_value' => !empty($conf['use_tail']),
    '#description' => t('If checked, this string will include all arguments. For example, if the path is "path/%" and the user visits "path/foo/bar", if this is not checked the string will be "foo". If it is checked the string will be "foo/bar".'),
  );
}

/**
 * Switch the placeholder based upon user settings.
 */
function ctools_string_path_placeholder($argument) {
  if (empty($argument['settings']['use_tail'])) {
    return '%pm_arg';
  }
  else {
    return '%pm_arg_tail';
  }
}