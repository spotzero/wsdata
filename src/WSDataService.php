<?php

namespace Drupal\wsdata;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Service for processing WSData requests.
 */
class WSDataService {
  use StringTranslationTrait;

  protected $error;
  protected $performance;
  protected $status;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entity_type_manager = $entity_type_manager;
    $this->performance = [
      'calls' => 0,
      'runtime' => 0.0,
      'log' => [],
    ];
    $this->status = [];
  }

  /**
   * {@inheritdoc}
   */
  public function __destruct() {
    if ($this->performance['calls'] > 0 and \Drupal::state()->get('wsdata_performance_log', 0)) {
      $message = $this->t(
        'WSData Performance - %calls calls in %runtime seconds.',
        [
          '%calls' => $this->performance['calls'],
          '%runtime' => round($this->performance['runtime'], 3),
        ]);
      $message .= "<br>\nCall list:\n<ol>\n";
      foreach ($this->performance['log'] as $log) {
        $method = '';
        if (isset($method)) {
          $method = ':' . $log['method'];
        }
        $message .= '<li>' . $log['wscall'] . $method . ' - ' . round($log['runtime'], 3) . "s (" . $log['cached'] . ")</li>\n";
      }
      $message .= '</ol>';
      \Drupal::logger('wsdata')->debug($message);
    }
  }

  /**
   * Call method to make the WSCall.
   */
  public function call($wscall, $method = NULL, $replacements = [], $data = NULL, $options = [], $key = NULL, $tokens = [], $cache_tag = []) {
    $this->status = [];
    if (!is_object($wscall)) {
      $wscall = $this->entity_type_manager->getStorage('wscall')->load($wscall);
    }
    $start = microtime(TRUE);
    $data = $wscall->call($method, $replacements, $data, $options, $key, $tokens, $cache_tag);
    $end = microtime(TRUE);

    $this->status = $wscall->lastCallStatus();
    if (\Drupal::state()->get('wsdata_debug_mode')) {
      ksm($this->status);
    }

    // Track performance information.
    $duration = $end - $start;
    $this->performance['calls']++;
    $this->performance['runtime'] += $duration;
    $this->performance['log'][] = [
      'wscall' => $wscall->label(),
      'method' => $method,
      'runtime' => $duration,
      'cached' => $this->status['cache']['debug'] ?? '',
    ];
    return $data;
  }

  public function getError() {
    if ($this->error) {
      $error = $this->error;
      $this->error = NULL;
      return $error;
    }
    return NULL;
  }

  /**
   * Generid WSCall setting form.
   */
  public function wscallForm($configurations = [], $wscall_option = NULL) {
    $wscalls = $this->entity_type_manager->getStorage('wscall')->loadMultiple();
    $options = ['' => $this->t('- Select -')];
    foreach ($wscalls as $wscall) {
      $options[$wscall->id()] = $wscall->label();
    }

    $element['wscall'] = [
      '#type' => 'select',
      '#title' => $this->t('Web Service Call'),
      '#options' => $options,
      '#required' => TRUE,
      '#ajax' => [
        'callback' => 'Drupal\wsdata\WSDataService::wscallConfigurationsReplacements',
        'wrapper' => 'wscall-replacement-tokens-wrapper',
      ],
      '#default_value' => (isset($configurations['wscall']) ? $configurations['wscall'] : '')
    ];

    // Fetch the replacement tokens for this wscall.
    $element['replacements'] = [
      '#id' => 'wscall-replacement-tokens-wrapper',
      '#type' => 'container',
    ];

    // Based on the wscall create the replacements section of the wscall.
    if (!empty($wscall_option)) {
      foreach ($wscalls[$wscall_option]->getReplacements() as $replacement) {
        $element['replacements'][$replacement] = [
          '#type' => 'textfield',
          '#title' => $replacement,
          '#default_value' => (isset($configurations['replacements'][$replacement]) ? $configurations['replacements'][$replacement] : '')
        ];
      }
    }

    $element['data'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Data'),
      '#default_value' => (isset($configurations['data']) ? $configurations['data'] : '')
    ];

    $element['returnToken'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Token to select'),
      '#description' => $this->t('Seperate element names with a ":" to select nested elements.'),
      '#default_value' => (isset($configurations['returnToken']) ? $configurations['returnToken'] : '')
    ];
    return $element;
  }

  /**
   * Expose the status of the last call.
   */
  public function lastCallStatus() {
    return $this->status;
  }

  /**
   * Ajax call back for the replacement pattern.
   */
  function wscallConfigurationsReplacements(array &$form, FormStateInterface $form_state) {
    if (isset($form['replacements'])) {
      return $form['replacements'];
    }
    elseif(isset($form['settings']['replacements'])) {
      return $form['settings']['replacements'];
    }
    else {
      return $form;
    }
  }
}
