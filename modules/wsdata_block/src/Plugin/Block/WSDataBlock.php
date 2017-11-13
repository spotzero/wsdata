<?php

/**
 * @file
 * Contains \Drupal\energy_och_calculation\Plugin\Block\ChCEmissions.
 */

namespace Drupal\wsdata_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Green House Gas Emissions' block.
 *
 * @Block(
 *   id = "wsdata_block",
 *   admin_label = @Translation("Wsdata block"),
 *   category = @Translation("wsdata")
 * )
 */
class WSDataBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    // Load the wscall entities.
    $wscalls = entity_load_multiple('wscall');
    $options = [];
    foreach ($wscalls as $wscall) {
      $options[$wscall->id()] = $wscall->label();
    }

    $form['wscall'] = [
      '#type' => 'select',
      '#title' => t('Web Service Call'),
      '#options' => $options,
      '#required' => TRUE,
      '#default_value' => $this->configuration['wscall'],
      '#ajax' => [
        'callback' => '::WSBlockReplacement',
        'wrapper' => 'wscall-replacement-tokens-wrapper',
      ],
    ];

    // Fetch the replacement tokens for this wscall.
    $form['replacements'] = [
      '#id' => 'wscall-replacement-tokens-wrapper',
      '#type' => 'container',
    ];
    // TODO: The ajax call back is not working so all of this is theoretical.
    if (!empty($this->configuration['wscall'])) {
      foreach ($wscalls[$this->configuration['wscall']]->getReplacements() as $replacement) {
        $form['replacements'][$replacement] = [
          '#type' => 'textfield',
          '#title' => $replacement,
          '#default_value' => isset($this->configuration['replacements']->replacements[$replacement]) ? $this->configuration['replacements']->replacements[$replacement] : '',
        ];
      }
    }

    $form['body'] = [
      '#type' => 'textarea',
      '#title' => t('Body'),
      '#default_value' => $this->configuration['body'],
    ];

    $form['returnToken'] = [
      '#type' => 'textfield',
      '#title' => t('Token to select'),
      '#default_value' => $this->configuration['returnToken'],
      '#description' => t('Seperate element names with a ":" to select nested elements.'),
    ];

    return $form;
  }

  /**
   * Ajax callback function.
   */
  public function WSBlockReplacement(array $form, FormStateInterface $form_state) {
    return $form['replacements'];
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['wscall'] = $form_state->getValue('wscall');
    // TODO: Loop thru the replacements and save them as an array.
    $this->configuration['replacements'] = $form_state->getValue('replacements');
    $this->configuration['body'] = $form_state->getValue('body');
    $this->configuration['returnToken'] = $form_state->getValue('returnToken');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Fetch the wscall.
    $wscall = entity_load('wscall', $this->configuration['wscall']);
    // Fetch the context from the page ?
    $result = $wscall->call(NULL, array(), $this->configuration['body'], array(), $this->configuration['returnToken']);

    $form['wsdata_block_data'] = [
      '#prefix' => '<div class="wsdata_block">',
      '#suffix' => '</div>',
      '#markup' => is_array($result) ? print_r($result, TRUE) : $result,
    ];

    return $form;
  }
}
