<?php

namespace Drupal\location_finder\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class LocationFinderForm extends FormBase {

  public function getFormId() {
    return 'location_finder_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['location'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Location'),
      '#description' => $this->t('Enter a location to find.'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Find Location'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $location = $form_state->getValue('location');
    drupal_set_message($this->t('Searching for: @location', ['@location' => $location]));
  }

}

?>