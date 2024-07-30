<?php

namespace Drupal\custom_address_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;

class AddressForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'custom_address_form';
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
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get form values.
    $country = $form_state->getValue('country');
    $city = $form_state->getValue('city');
    $postal_code = $form_state->getValue('postal_code');

    $api_response = my_custom_module_fetch_data($country,$city,$postal_code);
    $data = $api_response['locations'];
  
    // Process the data or return it in a response.

    $unset_filtered_arrays = [];

    $satday = 'http://schema.org/Saturday';
    $sunday = 'http://schema.org/Sunday';
    $days['Monday'] = 'http://schema.org/Monday';
    $days['Tuesday'] = 'http://schema.org/Tuesday';
    $days['Wednsday'] = 'http://schema.org/Wednesday';
    $days['Thursday'] = 'http://schema.org/Thursday';
    $days['Friday'] = 'http://schema.org/Friday';
    $days['Saturday'] = 'http://schema.org/Saturday';
    $days['Sunday'] = 'http://schema.org/Sunday';

    for($i=0;$i<count($data);$i++){
        
      $workingdays = [];
      $working_days_key=[];
      for ($j=0;$j<count($data[$i]['openingHours']);$j++){
          $workingdays[$j] = $data[$i]['openingHours'][$j]['dayOfWeek'];
          $working_days_key[] = array_search($data[$i]['openingHours'][$j]['dayOfWeek'], $days).': '.$data[$i]['openingHours'][$j]['opens'].' - '.$data[$i]['openingHours'][$j]['closes'];
    };
    $data[$i]['working_days'] = array_values(array_unique($working_days_key));
    
    // Finding not working locations on weekends
    if(!in_array($satday,$workingdays) && !in_array($sunday,$workingdays)){
        $unset_filtered_arrays[] = $i;
        }
    
    // Finding locations aving odd number in addressLocality
    $addresslocality = $data[$i]['place']['address']['addressLocality'];
    preg_match_all('!\d+\.*\d*!', $addresslocality, $matches);
    for($o=0;$o<count($matches[0]);$o++){
        if($matches[0][$o] %2 == 1){
            $unset_filtered_arrays[] = $i;
            break;
        }
     }
    }

    for($i=0;$i<count($unset_filtered_arrays);$i++){
        unset($data[$unset_filtered_arrays[$i]]);
    }

    $weekdaysonly = array_values($data);
    $response = [];

    for($i=0;$i<count($weekdaysonly);$i++){
    $response[$i]['locationName'] = $weekdaysonly[$i]['name'];
    $response[$i]['address'] = $weekdaysonly[$i]['place']['address'];
    $response[$i]['openingHours'] = $weekdaysonly[$i]['working_days'];

    }
    $yaml = Yaml::dump($response);
    echo '<pre>';
    echo $yaml;
    exit;
    \Drupal::messenger()->addMessage($yaml, 'status');

  }
}
