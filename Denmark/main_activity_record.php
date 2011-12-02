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
$row = $last_array_item;
           
            //<iati-activity xml:lang="xml:lang" default-currency="default-currency" hierarchy="hierarchy" last-updated-datetime="last-updated">
            $activity = $doc->createElement('iati-activity');
            $activity = $root->appendChild($activity);
              $activity->setAttribute('xml:lang', $default_lang); //set in defaults
              $activity->setAttribute('default-currency',$default_currency); //set in defaults
              $activity->setAttribute('hierarchy',$default_hierarchy); //set in defaults
              $activity->setAttribute('last-updated-datetime',$last_updated); //set in defaults
            
            
            //<reporting-org ref="reporting-org_ref" type="reporting-org_type">reporting-org_name</reporting-org>
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

            
            //<iati-identifier>reporting-org_ref-activityid</iati-identifier>
            $activity_id = $row['ActivityId'];
            $previous_activity_id = $activity_id;
              
            $id = $doc->createElement('iati-identifier');
            $id = $activity->appendChild($id);
            $value = $doc->createTextNode($reporting_org_ref . '-' . $activity_id);
            //print_r($value);
            $value = $id->appendChild($value);
            
            
            //<other-identifier owner-ref="CRS" owner-name="OECD DAC CRS">crsid</other-identifier>
            $other_identifier_text = $row['crsid'];
            
            $other_identifier = $doc->createElement('other-identifier');
            $other_identifier = $activity->appendChild($other_identifier);
            $value = $doc->createTextNode($other_identifier_text);
            $value = $other_identifier->appendChild($value);
              $other_identifier->setAttribute('owner-ref','CRS'); 
              $other_identifier->setAttribute('owner-name','OECD DAC CRS'); 
            
            
            //<other-identifier owner-ref="reporting-org_ref" owner-name="reporting-org_name">projectnumber</other-identifier>
            $project_number = $row['projectnumber'];
            
            $other_identifier = $doc->createElement('other-identifier');
            $other_identifier = $activity->appendChild($other_identifier);
            $value = $doc->createTextNode($project_number);
            $value = $other_identifier->appendChild($value);
              $other_identifier->setAttribute('owner-ref',$reporting_org_ref); 
              $other_identifier->setAttribute('owner-name',$reporting_org_name); 

            
            //<title>projecttitle ELSE shortdescription</title>
              if (strlen($row['projecttitle']) >2) { 
              $project_title = $row['projecttitle'];
            } else {
              $project_title = $row['shortdescription'];
            }
            
            $title = $doc->createElement('title');
            $title= $activity->appendChild($title);
            $value = $doc->createTextNode($project_title);
            $value = $title->appendChild($value);
              $title->setAttribute('xml:lang', 'en');

            
            //<description>longdescription ELSE shortdescription</description>
              if (strlen($row['longdescription']) >2) {
              $description_text = $row['longdescription'];
            } else {
              $description_text = $row['shortdescription'];
            }
            //echo $description_text; die;
            
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
              
            
            //<activity-date type="start-planned" iso-date="expectedstartdate"/>
            //<activity-date type="end-planned" iso-date="completiondate"/>
            //Get dates in european string format style i.e. replace / with - and if strtotime still can't parse it 
            //don't set date to 1970!
            if (!empty($row['expectedstartdate'])) {
              $european_time_string_exstart = preg_replace('/\//', "-", $row['expectedstartdate']);
              if (strtotime($european_time_string_exstart)) {
                $activity_date_start_planned = date("Y-m-d",strtotime($european_time_string_exstart));
              }
            } else {
                $activity_date_start_planned = NULL;
            }
            
            if (!empty($row['completiondate'])) {
                $european_time_string_completed = preg_replace('/\//', "-", $row['completiondate']);
                if (strtotime($european_time_string_completed)) {
                  $activity_date_end_planned =  date("Y-m-d",strtotime($european_time_string_completed));
                }
            } else {
                $activity_date_end_planned = NULL;
            }
            
            $activity_dates = array("date1" => array( "type" => "start-planned",
                                                      "iso" => $activity_date_start_planned),
                                    "date2" => array( "type" => "end-planned",
                                                                "iso" => $activity_date_end_planned)
                                   );
                                   
            foreach ($activity_dates as $date) {
              if ($date['iso'] != NULL) {
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
            }
            
            //<participating-org role="funding" ref="funding-org_ref" type="funding-org_type">funding-org_name</participating-org>
            //<participating-org role="extending"  ref="funding-org_ref-agencycode" type="reporting-org_type">agencyname</participating-org>
            //<participating-org role="implementing" ref="channelcode">channelreportedname OR channelname</participating-org>
            $channel_code =$row['channelcode'];
            if (strlen($row['channelname'])>2) {
              $participating_org_text = $row['channelname'];
            } else {
              $participating_org_text = $row['channelreportedname'];
            }
            $agency_name = $row['agencyname'];
            if ($agency_name == NULL) {
              $agency_name = $reporting_org_name;
            }
            $agency_code = $row['agencycode'];
            
            $participating_orgs = array("org1" => array("role" => "funding",
                                                        "ref" => $funding_org_ref,     //set in defaults
                                                        "type" => $funding_org_type,   //set in defaults
                                                        "text" => $funding_org_name),  //set in defaults
                                        "org2" => array("role" => "extending",
                                                        "ref" => $funding_org_ref . "-" . $agency_code,     
                                                        "type" => $reporting_org_type,   //set in defaults
                                                        "text" => $agency_name),  
                                        "org3" => array("role" => "implementing",
                                                        "ref" => $channel_code,     
                                                        //"type" => $reporting_org_type,   
                                                        "text" => $participating_org_text)
                                        );  

            foreach ($participating_orgs as $org) {
              if ($org['role'] == "implementing" && ($org['ref'] ==NULL && $org['text'] == NULL)) {
                continue;
              }
              
              $participating_org = $doc->createElement('participating-org');
              $participating_org= $activity->appendChild($participating_org);
              if (!empty($org['text'])) { //$org['text'] = "!!!!!FIX ME!!!!"; }
                $value = $doc->createTextNode($org['text']); 
                $value = $participating_org->appendChild($value);
              }
                if (isset($org['role'])) {
                  $participating_org->setAttribute('role',$org['role']);
                }
                if (isset($org['type'])) {
                  $participating_org->setAttribute('type',$org['type']);
                }
                if (isset($org['ref'])) {
                  $participating_org->setAttribute('ref',$org['ref']); 
                }
            }  

            
            //<recipient-country code="recipientcode">recipientname</recipient-country>
            //<recipient-region code="recipientcode">recipientname</recipient-region>  
            $recipient_region_text = $row['recipientname'];
            $recipient_country_text = $row['recipientname'];
            $recipient_region_code = $row['recipientcode'];
            $recipient_country_code =$row['recipientcode'];
            
            //if (in_array($recipient_country_code, array_keys($countries)) && ) { //if code is an ISO-2 Country code, it's a country
            if (isset($countries[$recipient_country_code][0]) && $countries[$recipient_country_code][0] != NULL) {
                $recipient_country = $doc->createElement('recipient-country');
                $recipient_country= $activity->appendChild($recipient_country);
                if (empty($recipient_country_text)) {
                  $recipient_country_text = $countries[$recipient_country_code][1];
                }
                if ($recipient_country_text != NULL) {
                  $value = $doc->createTextNode($recipient_country_text);
                  $value = $recipient_country->appendChild($value);
                }
                  $recipient_country->setAttribute('code',$countries[$recipient_country_code][0]);
            } else {                                                          //if not, it's a region
                if (!empty($recipient_region_text) || !empty($recipient_region_code)) {
                  $recipient_region = $doc->createElement('recipient-region');
                  $recipient_region= $activity->appendChild($recipient_region);
                  if (empty($recipient_region_text)) {
                    $recipient_region_text = $countries[$recipient_region_code][1];
                  }
                  if ($recipient_region_text != NULL) {
                    $value = $doc->createTextNode($recipient_region_text);
                    $value = $recipient_region->appendChild($value);
                  }
                    if (!empty($recipient_region_code)) {
                      $recipient_region->setAttribute('code',$recipient_region_code);
                    }
                }
            }
            

            /*
            $activity_website = $doc->createElement('activity-website');
            $activity_website= $activity->appendChild($activity_website);
            $value = $doc->createTextNode($row['website']);
            $value = $activity_website->appendChild($value);
            */


          //<location><name>geography</name></location>
            $geography  = $row['geography'];
            
            if ($geography !=NULL) {
              $location = $doc->createElement('location');
              $location = $activity->appendChild($location);
              
                $location_name = $doc->createElement('name');
                $location_name = $location->appendChild($location_name);
                $value = $doc->createTextNode($geography);
                $value = $location_name->appendChild($value); 
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
           

            //<sector code="purposecode">purposename</sector>
            $sector_code = $row['purposecode'];
            $sector_text = $row['purposename'];
            //$sector_vocabulary
            if (!empty($sector_code) || !empty($sector_text)) {
              $sector = $doc->createElement('sector');
              $sector= $activity->appendChild($sector);
              if (!empty($sector_text)) {
                $value = $doc->createTextNode($sector_text);
                $value = $sector->appendChild($value);
              }
                if (isset($sector_vocabulary)) {
                    $sector->setAttribute('vocabulary',$sector_vocabulary); 
                }
                if (!empty($sector_code)) {
                  $sector->setAttribute('code',$sector_code);
                }
            }
              
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
              if (!empty($marker['significance'])) {
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
            }  

            //<collaboration-type code="bi_multi"/>
            $bi_multi = $row['bi_multi'];
            if (!empty($bi_multi)) {
              $collaboration_type = $doc->createElement('collaboration-type');
              $collaboration_type = $activity->appendChild($collaboration_type);
                $collaboration_type->setAttribute('code',$bi_multi);
            }
            
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

            //<default-aid-type code="aid_t"/>
            $default_aid_type_code = $row['aid_t'];
            if (!empty($default_aid_type_code)) {
              $default_aid_type = $doc->createElement('default-aid-type');
              $default_aid_type= $activity->appendChild($default_aid_type);
              //$value = $doc->createTextNode($row['default-aid-type']);
              //$value = $default_aid_type->appendChild($value);
                $default_aid_type->setAttribute('code',$default_aid_type_code);
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
       
?>
