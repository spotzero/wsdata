<?php

namespace Drupal\wsdata_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\wsdata\WSDataService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Green House Gas Emissions' block.
 *
 * @Block(
 *   id = "wsdata_block",
 *   admin_label = @Translation("Wsdata Block"),
 *   category = @Translation("wsdata")
 * )
 */
class WSDataBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Entity Type Manager for loading.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * WSData Service.
   *
   * @var Drupal\wsdata\WSDataService
   */
  protected $wsdata;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, WSDataService $wsdata) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
    $this->wsdata = $wsdata;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('wsdata')
    );
  }

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

    $elements = $this->wsdata->wscallForm($this->configuration, $wscall);
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
    $wscall = $this->entityTypeManager->getStorage('wscall')->load($this->configuration['wscall']);
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
    $result = $this->wsdata->call($this->configuration['wscall'], NULL, $this->configuration['replacements'], $this->configuration['data'], [], $this->configuration['returnToken']);

    $form['wsdata_block_data'] = [
      '#prefix' => '<div class="wsdata_block">',
      '#suffix' => '</div>',
      '#markup' => is_array($result) ? print_r($result, TRUE) : $result,
    ];

    return $form;
  }

}
