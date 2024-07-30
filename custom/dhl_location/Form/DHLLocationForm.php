<?php

namespace Drupal\dhl_location\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Yaml\Yaml;

class DHLLocationForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dhl_location_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['country'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Country'),
      '#required' => TRUE,
    ];

    $form['city'] = [
      '#type' => 'textfield',
      '#title' => $this->t('City'),
      '#required' => TRUE,
    ];

    $form['postal_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Postal Code'),
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Find Locations'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $country = $form_state->getValue('country');
    $city = $form_state->getValue('city');
    $postal_code = $form_state->getValue('postal_code');

    $api_response = fetch_dhl_api_data($country, $city, $postal_code);

    $filtered_locations = $this->filterLocations($api_response);

    $yaml = Yaml::dump($filtered_locations);
    echo $yaml;
    \Drupal::messenger()->addMessage($yaml, 'status');
  }

  

  /**
   * Filter locations.
   */
  private function filterLocations(array $locations) {

    $satday = 'http://schema.org/Saturday';
    $sunday = 'http://schema.org/Sunday';
    $unset = [];

    $days['Monday'] = 'http://schema.org/Monday';
    $days['Tuesday'] = 'http://schema.org/Tuesday';
    $days['Wednsday'] = 'http://schema.org/Wednesday';
    $days['Thursday'] = 'http://schema.org/Thursday';
    $days['Friday'] = 'http://schema.org/Friday';
    $days['Saturday'] = 'http://schema.org/Saturday';
    $days['Sunday'] = 'http://schema.org/Sunday';
    /*return array_filter($locations, function ($location) {
      // Check if location works on weekends
      if (isset($location['workDays']) && !in_array('Saturday', $location['workDays']) && !in_array('Sunday', $location['workDays'])) {
        return FALSE;
      }

      // Check if address has an odd number
      if (preg_match('/\d/', $location['address']) && (int) $location['address'] % 2 !== 0) {
        return FALSE;
      }

      return TRUE;
    }); */
  }
}
