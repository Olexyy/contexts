<?php

namespace Drupal\contexts\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ContextForm.
 */
class ContextForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $form = parent::form($form, $form_state);
    $context = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $context->label(),
      '#description' => $this->t("Label for the Context."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $context->id(),
      '#machine_name' => [
        'exists' => '\Drupal\contexts\Entity\Context::load',
      ],
      '#disabled' => !$context->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $context = $this->entity;
    $status = $context->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Context.', [
          '%label' => $context->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Context.', [
          '%label' => $context->label(),
        ]));
    }
    $form_state->setRedirectUrl($context->toUrl('collection'));
  }

}
