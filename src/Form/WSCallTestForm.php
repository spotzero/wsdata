<?php

namespace Drupal\wsdata\Form;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\wsdata\WSDataService;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Class WSCallForm.
 *
 * @package Drupal\wsdata\Form
 */
class WSCallTestForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function __construct(WSDataService $wsdata) {
    $this->wsdata = $wsdata;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      $container->get('wsdata')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['response'] = [
      '#prefix' => '<pre>',
      '#markup' => $form_state->getValue('wscall_response', ''),
      '#suffix' => '</pre>'
    ];

    return $form;
  }

  /**
   * Call the wscall.
   */
  public function call(array $form, FormStateInterface $form_state) {
    $form_state->setValue('wscall_response', $this->entity->id());
    $form_state->setRebuild(TRUE);
    /*$form['response'] = [
      //'#prefix' => '<pre>',
      '#markup' => $this->entity->id(),
      //'#markup' => Xss::filter($this->wsdata->call($this->entity->id())),
    //  '#suffix' => '</pre>'
    ];
    */
  }


  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Call'),
      '#submit' => array('::call'),
    );
    return $actions;
  }
}
