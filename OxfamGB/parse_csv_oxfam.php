<?php
/*
 *=== International Aid Transparency Initiative (IATI) CRS Converter Information ===
 * 
 *    International Aid Transparency Initiative (IATI) CRS Converter is an application to generate IATI compliant
 *    location XML from a specific set of data supplied by CRS systems.
 *    This may be useful for other datasets and other transformations.
 *
 *    This file is part of International Aid Transparency Initiative (IATI) CRS Converter.
 *    Copyright (C) 2011 David Carpenter
 *    Made and paid for by Development Initiatives (http://www.devinit.org/)
 *
 *    International Aid Transparency Initiative (IATI) CRS Converter is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    International Aid Transparency Initiative (IATI) CRS Converter is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with International Aid Transparency Initiative (IATI) CRS Converter.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 *Contact Information
 *caprenter@gmail.com
 *
*/
?>
<?php
          /*Column headers for Oxfam
           * 
           * projectid
           * title
           * description
           * startdate
           * enddate
           * impactlevel
           * countrycode
           * countryname
           * location
           * aim1
           * aim2
           * aim3
           * aim4
           * aim5
           * backspend
           * currentspend
           * budget
           * commitment
           */
?>

<?php
//if csv file contains headers and you want to INCLUDE them call the script with
//php parse_csv.php headers=TRUE
$headers = $_SERVER['argv'][1];

$file  = "oxfam_test.csv"; //input file
$file  = "oxfam.csv";
$save_file_as = "oxml/oxfam_";

$region_codes_array = array(); //we'll use this to store a list of regions and countries represented in the data

//Parse the csv file and get the whole thing into a great big array
if (($handle = fopen($file, "r")) !== FALSE) {
    
    if($headers == TRUE) {
      $row1 = fgetcsv($handle, 0, ';','"'); // read and store the first line
      //fgets($handle); // read and ignore the first line
      //print_r($row1);
    } 
    
    while (($data = fgetcsv($handle, 0, ';','"')) !== FALSE) { //set string length parameter to 0 lets us pull in very long lines.
      //data[] is an array of values we need to create a big array of type:
      // array("projectid"= {value from column 1 of csv},
      //       "title = = {value from column 2 of csv},
      //        ........
      foreach ($row1 as $key=>$value) { //e.g. $row1[0] = projectid
        $this_row_to_array[$value] = utf8_encode($data[(int)$key]);
        //e.g. $this_row_to_array['projectid'] = utf8_encode($data[0])
        //Create our array of region/county codes
        if ($value == "countrycode") {
          array_push($region_codes_array,utf8_encode($data[(int)$key]));
        }
        
      }   
      $crs_data[] = $this_row_to_array; //an array of the rows
      //print_r($crs_data);  
      //die;
    }
    fclose($handle);
}
//print_r($codes);//die;
//print_r($region_codes_array);die;

//Get the IATI Country code list into a usable array of "code"=>"name" format
$country_data = file_get_contents("Country.json"); 
$country_data = json_decode($country_data, true);
//print_r($country_data['codelist']);
//die;
foreach ($country_data['codelist']['Country'] as $country) {
  $countries[$country['code']] = $country['name'];
}
//print_r($countries);//die;

//Some default variables really more like constants

//<iati-activities version="1.00" generated-datetime="2011-11-22">
//<iati-activity xml:lang="en" default-currency="GBP" hierarchy="2" last-updated-datetime="2011-11-22">
//<reporting-org ref="GB-CHC-202918" type="21">Oxfam GB</reporting-org>
//<iati-identifier>GB-CHC-202918-projectid</iati-identifier>

$default_lang = "en";
$default_currency = "GBP";
$default_hierarchy =	"2";
$last_updated	= "2011-11-22T00:00:00";
$reporting_org_ref = "GB-CHC-202918";
$reporting_org_type ="21";
$reporting_org_name = "Oxfam GB";
$funding_org_ref = "GB-CHC-202918";
$funding_org_type	= "21";
$funding_org_name = "Oxfam GB";

$registry_record = FALSE;

$registry_record_attributes = array('id' => '',
                                    'url' => '',
                                    'publisher-id' => '',
                                    'publisher-type' => '',
                                    'contact' => '',
                                    'donor-id' => '',
                                    'donor-type' => '',
                                    'donor-country' => '',
                                    'title' => '',
                                    'activity-period' => '',
                                    'data-updated' => '',
                                    'record-updated' => '',
                                    'verified' => '',
                                    'format' => '',
                                    'licence' => ''
                                    );
                                    
                          
//We're going to use these regions/countires to loop through the data and create an xml file for each 
//county/region. So first we need to tidy the array up a little
$region_codes_array = array_unique($region_codes_array);
//Just clear out any empty references. 
foreach ($region_codes_array as $key=>$value) {
  if (trim($value) == NULL) {
    unset($region_codes_array[$key]);
  }
}

foreach ($region_codes_array as $region_file) {                                                                       
                                          
      // create a new XML document (for each country)
      $doc = new DomDocument('1.0','UTF-8');
      //$doc = new DomDocument('1.0');

      //<iati-activities version="1.00" generated-datetime="2011-11-22">
      $root = $doc->createElement('iati-activities');
      $root = $doc->appendChild($root);
        $root->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $root->setAttribute('xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');
        $root->setAttribute('generated-datetime', date("Y-m-d")."T".date("H:i:sP"));
        $root->setAttribute('version', '1.00');

      if ($registry_record == TRUE) { //set earlier manually
        $registry_record = $doc->createElement('registry-record');
        $registry_record = $root->appendChild($registry_record);
          foreach ($registry_record_attributes as $key => $value) {
            if ($value !="") {
              $registry_record->setAttribute($key, $value);
            }  
          }
      }
      
      //Loop through the data we stored from the csv
      foreach ($crs_data as $row) {
        if ( $row['countrycode'] == $region_file) {
        
            //<iati-activity xml:lang="en" default-currency="GBP" hierarchy="2" last-updated-datetime="2011-11-22">
            $activity = $doc->createElement('iati-activity');
            $activity = $root->appendChild($activity);
              $activity->setAttribute('xml:lang', $default_lang); //set in defaults
              $activity->setAttribute('default-currency',$default_currency); //set in defaults
              $activity->setAttribute('hierarchy',$default_hierarchy); //set in defaults
              $activity->setAttribute('last-updated-datetime',$last_updated); //set in defaults
            
            //<reporting-org ref="GB-CHC-202918" type="21">Oxfam GB</reporting-org>
            $reporting_org = $doc->createElement('reporting-org');
            $reporting_org= $activity->appendChild($reporting_org);
            $value = $doc->createTextNode($reporting_org_name); //set in defaults
            $value = $reporting_org->appendChild($value);
              if ($reporting_org_type) {
                $reporting_org->setAttribute('type',$reporting_org_type); //set in defaults
              }
              if ($reporting_org_ref) {
                $reporting_org->setAttribute('ref',$reporting_org_ref); //set in defaults
              }
              
              
            //<iati-identifier>GB-CHC-202918-projectid</iati-identifier>
            $activity_id =$row['projectid'];
            
            $id = $doc->createElement('iati-identifier');
            $id = $activity->appendChild($id);
            $value = $doc->createTextNode($reporting_org_ref . '-' . $activity_id);
            $value = $id->appendChild($value);
            
            /*
            //<other-identifier owner-ref="CRS" owner-name="OECD DAC CRS">crsid</other-identifier>
            $other_identifier = $doc->createElement('other-identifier');
            $other_identifier = $activity->appendChild($other_identifier);
            $value = $doc->createTextNode($other_identifier_text);
            $value = $other_identifier->appendChild($value);
              $other_identifier->setAttribute('owner-ref','CRS'); 
              $other_identifier->setAttribute('owner-name','OECD DAC CRS'); 
            
            //<other-identifier owner-ref="reporting-org_ref" owner-name="reporting-org_name">projectnumber</other-identifier>
            $other_identifier = $doc->createElement('other-identifier');
            $other_identifier = $activity->appendChild($other_identifier);
            $value = $doc->createTextNode($project_number);
            $value = $other_identifier->appendChild($value);
              $other_identifier->setAttribute('owner-ref',$reporting_org_ref); 
              $other_identifier->setAttribute('owner-name',$reporting_org_name); 
             */ 
            
            //<title>title</title>
            $project_title = $row['title'];
            
            $title = $doc->createElement('title');
            $title= $activity->appendChild($title);
            $value = $doc->createTextNode($project_title);
            $value = $title->appendChild($value);
              $title->setAttribute('xml:lang', 'en');

            //<description>description</description>
            $description_text = $row['description'];
            
            $description = $doc->createElement('description');
            $description = $activity->appendChild($description);
            $value = $doc->createTextNode($description_text);
            $value = $description->appendChild($value);  
              $description->setAttribute('xml:lang', 'en');
            
            //<activity-status code="2">Implementation</activity-status>
            $activity_status = $doc->createElement('activity-status');
            $activity_status = $activity->appendChild($activity_status);
            $value = $doc->createTextNode("Implementation");
            $value = $activity_status->appendChild($value);  
              $activity_status->setAttribute('code', '2');
              
              
            //<activity-date type="start-planned" iso-date="startdate"/>  Convert startdate from DD/MM/YYYY to YYYY-MM-DD
            //<activity-date type="end-planned" iso-date="enddate"/>     Convert enddate from DD/MM/YYYY to YYYY-MM-DD
            $activity_date_start_actual = date("Y-m-d",strtotime($row['startdate']));
            $activity_date_end_planned =  date("Y-m-d",strtotime($row['enddate']));
            //$activity_date_start_planned = date("Y-m-d",strtotime($row['expectedstartdate']));
              
            $activity_dates = array("date1" => array( "type" => "start-planned",
                                                      "iso" => $activity_date_start_actual),
                                    "date2" => array( "type" => "end-planned",
                                                                "iso" => $activity_date_end_planned)
                                   );
            
            foreach ($activity_dates as $date) {
              $activity_date = $doc->createElement('activity-date');
              $activity_date= $activity->appendChild($activity_date);
              //$value = $doc->createTextNode($row['activity_date']); 
              //$value = $activity_date->appendChild($value);
                if ($date['type']) {
                  $activity_date->setAttribute('type',$date['type']); 
                }
                if ($date['iso']) {
                  $activity_date->setAttribute('iso-date',$date['iso']); 
                }
            }
            
            
            //<participating-org role="funding" ref="GB-CHC-202918" type="21">Oxfam GB</participating-org>
            //<participating-org role="implementing" ref="GB-CHC-202918" type="21">Oxfam GB</participating-org>
            /*
            $channel_code =$row['channelcode'];
            if (strlen($row['channelname'])>2) {
              $participating_org_text = $row['channelname'];
            } else {
              $participating_org_text = $row['channelreportedname'];
            }
            */
            
            $participating_orgs = array("org1" => array("role" => "funding",
                                                        "ref" => $funding_org_ref,     //set in defaults
                                                        "type" => $funding_org_type,   //set in defaults
                                                        "text" => $funding_org_name),  //set in defaults
                                        //"org2" => array("role" => "implementing",
                                        //                "type" => $funding_org_type,   //set in defaults
                                        //                "text" => $funding_org_name),  //set in defaults
                                        );  

            foreach ($participating_orgs as $org) {
              if ($org['role'] == "implementing" && ($org['ref'] ==NULL && $org['text'] == NULL)) {
                continue;
              }
              
              $participating_org = $doc->createElement('participating-org');
              $participating_org= $activity->appendChild($participating_org);
              if (!$org['text']) { $org['text'] = "!!!!!FIX ME!!!!"; }
              $value = $doc->createTextNode($org['text']); 
              $value = $participating_org->appendChild($value);
                if ($org['role']) {
                  $participating_org->setAttribute('role',$org['role']);
                }
                if ($org['type']) {
                  $participating_org->setAttribute('type',$org['type']);
                }
                if ($org['ref']) {
                  $participating_org->setAttribute('ref',$org['ref']); 
                }
            }  

            /*
            $activity_website = $doc->createElement('activity-website');
            $activity_website= $activity->appendChild($activity_website);
            $value = $doc->createTextNode($row['website']);
            $value = $activity_website->appendChild($value);
            */
            
            //<recipient-country code="countrycode">countryname</recipient-country>   If countrycode is alpha then use it else exclude element
            //<recipient-region code="countrycode">countryname</recipient-region>     If countrycode is numeric then use it else exclude element

            $recipient_region_text = $row['countryname'];
            $recipient_country_text = $row['countryname'];
            $recipient_region_code = $row['countrycode'];
            $recipient_country_code =$row['countrycode'];
            
            if (!is_numeric($recipient_country_code)) {                               //if code is NOT numeric we assume it is alpha and use it 
                $recipient_country = $doc->createElement('recipient-country');
                $recipient_country= $activity->appendChild($recipient_country);
                $value = $doc->createTextNode($recipient_country_text);
                $value = $recipient_country->appendChild($value);
                  $recipient_country->setAttribute('code',$recipient_country_code);
            } else {                                                                   //if it is numeric then it's a region
                $recipient_region = $doc->createElement('recipient-region');
                $recipient_region= $activity->appendChild($recipient_region);
                $value = $doc->createTextNode($recipient_region_text);
                $value = $recipient_region->appendChild($value);
                  $recipient_region->setAttribute('code',$recipient_region_code);
            }
            

            //<location><description>location</name></location>
            $geography  = $row['location'];
            
            if ($geography !=NULL) {
              $location = $doc->createElement('location');
              $location = $activity->appendChild($location);
                /*
                $location_name = $doc->createElement('name');
                $location_name = $location->appendChild($location_name);
                $value = $doc->createTextNode($geography);
                $value = $location_name->appendChild($value); 
                */
                $location_description = $doc->createElement('description');
                $location_description = $location->appendChild($location_description);
                $value = $doc->createTextNode($geography);
                $value = $location_description->appendChild($value); 
            }
            /*$administrative = $doc->createElement('administrative');
            $administrative = $location->appendChild($administrative);
            
            //Country attribute...
            //Find country code
            if ($geo['country'] !=NULL) {
              //Find corresponding key to Country string in $countries array
              switch ($geo['country']) {
                 case 'Bosnia and Herzegovina':
                  $geo['country'] = 'Bosnia-Herzegovina';
                  break;
                case 'Central African Republic':
                  $geo['country'] = 'Central African Rep.';
                  break;
                case 'Congo, Democratic Republic of':
                case 'Congo, Republic of':
                  $geo['country'] = 'Congo, Dem. Rep.';
                  break;
                case 'Gambia, The':
                  $geo['country'] = 'Gambia';
                  break;
                //case 'Kosovo':
                 // $geo['country'] = '';
                //  break;
                case 'Lao People\'s Democratic Republic':
                  $geo['country'] = 'Laos';
                  break;
                //case 'Nepal':
                  //$geo['country'] = 'Gambia';
                  //break;
                case 'Sao Tome and Principe':
                  $geo['country'] = 'Sao Tome & Principe';
                  break;
                case 'St. Vincent and the Grenadines':
                  $geo['country'] = 'St.Vincent & Grenadines';
                  break;
                case 'Vietnam':
                  $geo['country'] = 'Viet Nam';
                  break;
                case 'Yemen, Republic of':
                  $geo['country'] = 'Yemen';
                  break;
                case 'Zambia ':
                  $geo['country'] = 'Zambia';
                  break;
                }
                
                
              //Special cases for countries not on the code list
              if ($geo['country'] == 'Nepal') {
                $code = 'NP';
              } elseif ($geo['country'] == 'Kosovo') {
                $code = 'XK';
              } else {
                $code = array_search($geo['country'], $countries);
              }
              //echo $key;
              if ($code != FALSE) { //array_search above returns False if not found
                $administrative->setAttribute('country', $code);
              } else {
                array_push($missing_codes,$geo['country']);
              }
            }
            
            //adm codes
            if ($geo['adm2_code'] !=NULL) {
              //$administrative->setAttribute('adm2', $geo['adm2_code']);
              $administrative->setAttribute('adm2', $geo['adm2_code']);
            }
            if ($geo['adm1_code'] !=NULL) {
              //$administrative->setAttribute('adm1', $geo['adm1_code']);
              $administrative->setAttribute('adm1', $geo['adm1_code']);
            }

            
            $administrative_text = $geo['adm2'] . ',' . $geo['adm1']. ',' . $geo['country'];
            $administrative_text = trim($administrative_text,",");
            $value = $doc->createTextNode($administrative_text);
            $value = $administrative->appendChild($value);
            
            //reset - not needed??
            $geo['country'] = NULL;
            
            //Coordinates element...
            if ($geo['lat'] != NULL && $geo['lng'] != NULL) {
              $co_ords = $doc->createElement('coordinates');
              $co_ords = $location->appendChild($co_ords);
              $co_ords->setAttribute('latitude', $geo['lat']);
              $co_ords->setAttribute('longitude', $geo['lng']);
              if ($geo['accuracy'] !=NULL) {
                $co_ords->setAttribute('precision', $geo['accuracy']);
              }
            }
            
            //gazetter entry - all taken from the GEOCODES column
            if ($geo['geoname_id'] !=NULL) {
              $gazetteer = $doc->createElement('gazetteer-entry');
              $gazetteer = $location->appendChild($gazetteer);
              $gazetteer->setAttribute('gazetteer-ref', 'GEO');
                
              $value = $doc->createTextNode($geo['geoname_id']);
              $value = $gazetteer->appendChild($value);
            }
            //Use Geoname for NAME element
            if ($geo['geoname'] !=NULL) {
              $name = $doc->createElement('name');
              $name = $location->appendChild($name);

              $value = $doc->createTextNode($geo['geoname']);
              $value = $name->appendChild($value);
            }
            */
            //What's misisng
            //description
            //location-type
            //
            //break;

            
            /*
            //<default-aid-type code="aid_t"/>
            $default_aid_type_code = $row['aid_t'];
            
            $default_aid_type = $doc->createElement('default-aid-type');
            $default_aid_type= $activity->appendChild($default_aid_type);
            //$value = $doc->createTextNode($row['default-aid-type']);
            //$value = $default_aid_type->appendChild($value);
              $default_aid_type->setAttribute('code',$default_aid_type_code);
            */

            //<sector vocabulary="oxfamgb" code="Aim1"percentage="aim1">Right to Sustainable Livelihoods</sector>	If aim1 else exclude element
            //<sector vocabulary="oxfamgb" code="Aim2"percentage="aim2">Right to Essential services</sector>	    If aim2 else exclude element
            //<sector vocabulary="oxfamgb" code="Aim3"percentage="aim3">Right to Life and Security</sector>	      If aim3 else exclude element
            //<sector vocabulary="oxfamgb" code="Aim4"percentage="aim4">Right to be heard</sector>	              If aim4 else exclude element  
            //<sector vocabulary="oxfamgb" code="Aim5"percentage="aim5">Right to Equity</sector>	                If aim5 else exclude element

            $sector_texts = array("Right to sustainable livelihoods",
                                  "Right to essential services",
                                  "Right to life and security",
                                  "Right to be heard" ,
                                  "Right to equity"
                                  );
            for ($i=1;$i<6;$i++) {
                $row['aim' .$i] = trim($row['aim' .$i]);
                //echo trim($row['aim' .$i]);
              
              if (is_numeric($row['aim' .$i])) {
                $sector_code = "Aim" . $i;
                $percentage= $row['aim'.$i];
                $sector_text = $sector_texts[$i-1];
              
                $sector_vocabulary = "oxfamgb";

                $sector = $doc->createElement('sector');
                $sector= $activity->appendChild($sector);
                $value = $doc->createTextNode($sector_text);
                $value = $sector->appendChild($value);
                  if ($sector_vocabulary) {
                    $sector->setAttribute('vocabulary',$sector_vocabulary); 
                  }
                  if (isset($sector_code)) {
                    $sector->setAttribute('code',$sector_code);
                  }
                  if (isset($percentage)) {
                    $sector->setAttribute('percentage',$percentage);
                  }
              }
            }
            
            //                                                        
            //<budget type="2">	
            //<period-start iso-date="2011-04-01"/>	
            //<period-end iso-date="enddate"/>	                      Convert enddate from DD/MM/YYYY to YYYY-MM-DD
            //<value value-date="2011-04-01">budget</value></budget>	
            $budget_text = $row['budget'];
            $budget_text = (int)preg_replace("/,/","",$row['budget']);
              
            if ($budget_text != 0) {
              $enddate = date("Y-m-d",strtotime($row['enddate']));
            
              $budget = $doc->createElement('budget');
              $budget= $activity->appendChild($budget);
              //$value = $doc->createTextNode($budget_text);
              //$value = $budget->appendChild($value);
                $budget->setAttribute('type','2'); 
              
              
              $period_start = $doc->createElement('period-start');
              $period_start= $budget->appendChild($period_start);
                $period_start->setAttribute('iso-date','2011-04-01'); 
              
              $period_end = $doc->createElement('period-end');
              $period_end= $budget->appendChild($period_end);
                $period_end->setAttribute('iso-date',$date['iso']); 

              $budget_value = $doc->createElement('value');
              $budget_value = $budget->appendChild($budget_value);  
              $value = $doc->createTextNode($budget_text);
              $value = $budget_value->appendChild($value);
                if ($value_date) {
                    $budget_value->setAttribute('value-date','2011-04-01'); 
                }
            }
            
            
            
            //<transaction><transaction-type code="C">Commitment</transaction-type>	
            //<value value-date="startdate">backspend + currentspend + budget</value>	    Convert startdate from DD/MM/YYYY to YYYY-MM-DD
            //<transaction-date iso-date="startdate"/></transaction>	                    Convert startdate from DD/MM/YYYY to YYYY-MM-DD
            
            //<transaction><transaction-type code="E">Expenditure</transaction-type>	    If backspend else leave out
            //<value value-date="2010-03-31">backspend</value>	
            //<description>Expenditure between startdate and 2010-03-31</description>	    Convert startdate from DD/MM/YYYY to YYYY-MM-DD
            //<transaction-date iso-date="2010-03-31"/></transaction>	
            
            //<transaction><transaction-type code="E">Expenditure</transaction-type>	     If currentspend else leave out
            //<value value-date="2011-03-31">currentspend</value>	
            //<description>Expenditure between 2010-04-01 and 2011-03-31</description>	
            //<transaction-date iso-date="2010-03-31"/></transaction>	
            
            $transaction_data = array("trans1" => array(//"value" => (int)$row['backspend'] + (int)$row['currentspend'] + (int)$row['budget'],
                                                        "value" => (int)preg_replace("/,/","",$row['commitment']),
                                                        "code" => "C",     
                                                        "string" => "Commitment",
                                                        "value-date" => date("Y-m-d",strtotime($row['startdate'])),
                                                        "iso-date"=> date("Y-m-d",strtotime($row['startdate'])),
                                                        "description"=> "Total Project Value (Past Expenditure + Current and Future Budget)"),
                                      "trans2" => array("value" => (int)preg_replace("/,/","",$row['backspend']),
                                                        "code" => "E",     
                                                        "string" => "Expenditure",
                                                        "value-date" => "2010-03-31",
                                                        "iso-date"=> "2010-03-31",
                                                        "description"=> "Expenditure between " .date("Y-m-d",strtotime($row['startdate'])) . " and 2010-03-31"),
                                      "trans3" => array("value" => (int)preg_replace("/,/","",$row['currentspend']),
                                                        "code" => "E",     
                                                        "string" => "Expenditure",
                                                        "value-date" => "2011-03-31",
                                                        "iso-date"=> "2011-03-31",
                                                        "description"=> "Expenditure between 2010-04-01 and 2011-03-31"),
                                                                                
                                        );  


            foreach ($transaction_data as $t_data) {
              if (is_numeric($t_data['value']) && $t_data['value'] != 0) {
                $transaction = $doc->createElement('transaction');
                $transaction = $activity->appendChild($transaction);  
                
                  $transaction_type_code = $t_data['code'];
                  $transaction_type_text = $t_data['string'];
                  
                  $transaction_type = $doc->createElement('transaction-type');
                  $transaction_type = $transaction->appendChild($transaction_type);
                  $value = $doc->createTextNode($transaction_type_text );
                  $value = $transaction_type->appendChild($value);
                    if ($transaction_type_code) {
                        $transaction_type->setAttribute('code',$transaction_type_code); 
                    }
                
                  //<value value-date="year">usd_commitment</value>
                  $transaction_value_text = $t_data['value'];
                  $value_date = $t_data['value-date'];
                  
                  $transaction_value = $doc->createElement('value');
                  $transaction_value = $transaction->appendChild($transaction_value);  
                  $value = $doc->createTextNode($transaction_value_text );
                  $value = $transaction_value->appendChild($value);
                    if ($value_date) {
                        $transaction_value->setAttribute('value-date',$value_date); 
                    }
                  
                  if (isset($t_data['description'])) {
                    $transaction_description_text = $t_data['description'];
                    
                    $transaction_description = $doc->createElement('description');
                    $transaction_description = $transaction->appendChild($transaction_description);
                    $value = $doc->createTextNode($transaction_description_text );
                    $value = $transaction_description->appendChild($value);
                  }
                  //<transaction-date iso-date="year"/></transaction>
                  $transaction_date_text = $t_data['iso-date'];
                  
                  $transaction_date = $doc->createElement('transaction-date');
                  $transaction_date = $transaction->appendChild($transaction_date);  
                  //$value = $doc->createTextNode($row['$transaction_date_text']);
                  //$value = $transaction_date->appendChild($value);
                    if ($transaction_date_text) {
                        $transaction_date->setAttribute('iso-date',$transaction_date_text); 
                    }
                    

                  /*
                  $receiver_org = $doc->createElement('receiver-org');
                  $receiver_org= $transaction->appendChild($receiver_org);
                  if (!$row['receiver-org']) { $row['receiver-org'] = "!!!!!FIX ME!!!!"; }
                  $value = $doc->createTextNode($row['receiver-org']);
                  $value = $receiver_org->appendChild($value);
                    if ($receiver_org_ref) {
                        $receiver_org->setAttribute('ref',$receiver_org_ref); 
                    }
                  
                  $transaction_provider_org = $doc->createElement('provider-org');
                  $transaction_provider_org = $transaction->appendChild($transaction_provider_org);
                  $value = $doc->createTextNode($row['transaction_provider_org']);
                  $value = $transaction_provider_org->appendChild($value);
                    if ($transaction_provider_org_ref) {
                       $transaction_provider_org->setAttribute('ref',$transaction_provider_org_ref); 
                    }
                    
                  $disbursement_channel = $doc->createElement('disbursement-channel');
                  $disbursement_channel = $transaction->appendChild($disbursement_channel);
                  $value = $doc->createTextNode($row['disbursement-channel']);
                  $value = $disbursement_channel->appendChild($value);
                    if ($disbursement_channel_code) {
                        $disbursement_channel->setAttribute('code',$disbursement_channel_code); 
                    }*/
                }
            }
            
            
            
            /*
            //<policy-marker significance="gender"  code="1">Gender Equality</policy-marker>
            //<policy-marker significance="environment"  code="2">Aid to Environment</policy-marker>
            //<policy-marker significance="pdgg"  code="3">Participatory Development/Good Governance</policy-marker>
            //<policy-marker significance="trade"  code="4">Trade Development</policy-marker>
            //<policy-marker significance="biodiversity"  code="5">Biological Diversity</policy-marker>
            //<policy-marker significance="climate"  code="6">Climate Change</policy-marker>
            //<policy-marker significance="desertification"  code="8">Desertification</policy-marker>
            $gender = $row['gender'];
            $environment = $row['environment'];
            $pdgg = $row['pdgg'];
            $trade = $row['trade'];
            $biodiversity = $row['biodiversity'];
            $climate = $row['climate'];
            $desertification = $row['desertification'];
            
            $policy_markers = array("pm1" => array("significance" => $gender,
                                                        "code" => "1",
                                                        "text" => "Gender Equality"),
                                    "pm2" => array("significance" => $environment,
                                                        "code" => "2",
                                                        "text" => "Aid to Environment"),
                                    "pm3" => array("significance" => $pdgg,
                                                        "code" => "3",
                                                        "text" => "Participatory Development"),
                                    "pm4" => array("significance" => $trade,
                                                        "code" => "4",
                                                        "text" => "Trade Development"),
                                    "pm5" => array("significance" => $biodiversity,
                                                        "code" => "5",
                                                        "text" => "Biological Diversity"),
                                    "pm6" => array("significance" => $climate,
                                                        "code" => "6",
                                                        "text" => "Climate Change"),  
                                    "pm7" => array("significance" => $desertification,
                                                        "code" => "8",
                                                        "text" => "Desertification"),                                                                                                 
                                        ); 
              
            foreach ($policy_markers as $marker) {
              $policy_marker = $doc->createElement('policy-marker');
              $policy_marker = $activity->appendChild($policy_marker);
              //if (!$org['text']) { $org['text'] = "!!!!!FIX ME!!!!"; }
              $value = $doc->createTextNode($marker['text']); 
              $value = $policy_marker->appendChild($value);
                if ($marker['significance']) {
                  $policy_marker->setAttribute('significance',$marker['significance']);
                }
                if ($marker['code']) {
                  $policy_marker->setAttribute('code',$marker['code']);
                }
            }  
              
            //<collaboration-type code="bi_multi"/>
            $bi_multi = $row['bi_multi'];
            
            $collaboration_type = $doc->createElement('collaboration-type');
            $collaboration_type = $activity->appendChild($collaboration_type);
              $collaboration_type->setAttribute('code',$bi_multi);
            
            */


            /*
            $contact_info = $doc->createElement('contact-info');
            $contact_info= $activity->appendChild($contact_info);  
              $organisation = $doc->createElement('organisation');
              $organisation= $contact_info->appendChild($organisation);  
              $value = $doc->createTextNode($row['contact-info-organisation']);
              $value = $organisation->appendChild($value);
              
              $mailing_address = $doc->createElement('mailing-address');
              $mailing_address= $contact_info->appendChild($mailing_address);
              $value = $doc->createTextNode($row['mailing_address']);
              $value = $mailing_address->appendChild($value);
              
              $telephone = $doc->createElement('telephone');
              $telephone= $contact_info->appendChild($telephone);
              $value = $doc->createTextNode($row['telephone']);
              $value = $telephone->appendChild($value);
            */
            
            /*
            //<default-flow-type code="category"/>
            $default_flow_type_code = $row['category'];
            
            $default_flow_type = $doc->createElement('default-flow-type');
            $default_flow_type= $activity->appendChild($default_flow_type);
            //$value = $doc->createTextNode($row['default-flow-type']);
            //$value = $default_flow_type->appendChild($value);    
              if ($default_flow_type_code) {
                  $default_flow_type->setAttribute('code',$default_flow_type_code); 
              }
            
            //<default-finance-type code="finance_t"/>
            $default_finance_type_code = $row['finance_t'];

            $default_finance_type = $doc->createElement('default-finance-type');
            $default_finance_type= $activity->appendChild($default_finance_type);
            //$value = $doc->createTextNode($row['default-finance-type']);
            //$value = $default_finance_type->appendChild($value);    
              if ($default_finance_type_code) {
                  $default_finance_type->setAttribute('code',$default_finance_type_code); 
              }
            
            //<default-tied-status code="see notes"/>
            if ($row['usd_commitment']) {
              if ($row['usd_commitment'] == $row['usd_amountuntied']) {
                $default_tied_status_code = 5;
              }
            } else if ($row['usd_amountpartialtied']) {
                $default_tied_status_code = 3;
            } else {
                 $default_tied_status_code = 4;
            }
              
            $default_tied_status = $doc->createElement('default-tied-status');
            $default_tied_status= $activity->appendChild($default_tied_status);
            //$value = $doc->createTextNode($row['default-tied-status']);
            //$value = $default_tied_status->appendChild($value);    
              if ($default_tied_status_code) {
                  $default_tied_status->setAttribute('code',$default_tied_status_code); 
              }
            */
            
        } //end if country
     } //end for each


$doc->formatOutput = true;
$doc->save($save_file_as . $region_file .'.xml' ); //e.g. oxfam_ . GD . xml
$xml_string = $doc->saveXML();
echo $region_file . PHP_EOL;
//echo $xml_string;
}
//print_r(array_unique($missing_codes));
//print_r($missing_codes);
?>
