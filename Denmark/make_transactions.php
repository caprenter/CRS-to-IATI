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
//print_r($data); die;
foreach ($data as $row) {
  echo "hi";
              //Transactions need to be looped through from oldest to newest
              //<transaction><transaction-type code="C">Commitment</transaction-type>
              //<value value-date="year">usd_commitment</value>
              //<transaction-date iso-date="year"/></transaction>
              //<transaction><transaction-type code="D">Disbursement</transaction-type>
              //<value value-date="year">usd_disbursement</value>
              //<transaction-date iso-date="year"/></transaction>
              //<transaction><transaction-type code="LR">Loan Repayment</transaction-type>
              //<value value-date="year">usd_received</value>
              //<transaction-date iso-date="year"/></transaction>


              $transaction_data = array("trans1" => array("value" => (int)$row['usd_commitment'],
                                                          "code" => "C",     
                                                          "string" => "Commitment",
                                                          "alter-year" => "-01-01"),
                                        "trans2" => array("value" => (int)$row['usd_disbursement'],
                                                          "code" => "D",     
                                                          "string" => "Disbursement",
                                                          "alter-year" => "-12-31"),
                                        "trans3" => array("value" => (int)$row['usd_received'],
                                                          "code" => "LR",     
                                                          "string" => "Loan Repayment",
                                                          "alter-year" => "-12-31") 
                                          );  


              foreach ($transaction_data as $t_data) {
                if ($t_data['value'] !=0 && $t_data['value'] != NULL) {
                    
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
                      $value_date = $row['Year'] . $t_data['alter-year'];
                      
                      $transaction_value = $doc->createElement('value');
                      $transaction_value = $transaction->appendChild($transaction_value);  
                      $value = $doc->createTextNode($transaction_value_text );
                      $value = $transaction_value->appendChild($value);
                        if ($value_date) {
                            $transaction_value->setAttribute('value-date',$value_date); 
                        }
                        
                      //<transaction-date iso-date="year"/></transaction>
                      $transaction_date_text = $row['Year'] . $t_data['alter-year'];
                      
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
                  
                  } //end if value =0
              }
            }
?>
