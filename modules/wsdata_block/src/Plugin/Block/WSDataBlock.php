<?php

namespace Drupal\wsdata_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;

/**
 * Provides a 'Green House Gas Emissions' block.
 *
 * @Block(
 *   id = "wsdata_block",
 *   admin_label = @Translation("Wsdata Block"),
 *   category = @Translation("wsdata")
 * )
 */
class WSDataBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $wscall = $this->configuration['wscall'];

    // This can return a subfomstate and not a form_state interface.
    if ($form_state instanceof SubformState) {
      $form_state = $form_state->getCompleteFormState();
    }

    $form_state_wscall = $form_state->getValue('settings');
    if (isset($form_state_wscall['wscall'])) {
      $wscall = $form_state_wscall['wscall'];
    }

    $wsdata = \Drupal::service('wsdata');
    $elements = $wsdata->wscallForm($this->configuration, $wscall);
    $form = array_merge($form, $elements);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['wscall'] = $form_state->getValue('wscall');
    // Loop thru the replacements and save them as an array.
    $replacement = [];
    /* TODO: replace this workflow, this should be all done through the server
    and not the config entities directly. */
    $wscall = entity_load('wscall', $this->configuration['wscall']);
    foreach ($wscall->getReplacements() as $rep) {
      $replacement[$rep] = $form_state->getValue('replacements')[$rep];
    }
    $this->configuration['replacements'] = $replacement;
    $this->configuration['data'] = $form_state->getValue('data');
    $this->configuration['returnToken'] = $form_state->getValue('returnToken');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = [];
    $wsdata = \Drupal::service('wsdata');
    $result = $wsdata->call($this->configuration['wscall'], NULL, $this->configuration['replacements'], $this->configuration['data'], [], $this->configuration['returnToken']);

    $form['wsdata_block_data'] = [
      '#prefix' => '<div class="wsdata_block">',
      '#suffix' => '</div>',
      '#markup' => is_array($result) ? print_r($result, TRUE) : $result,
    ];

    return $form;
  }

}
