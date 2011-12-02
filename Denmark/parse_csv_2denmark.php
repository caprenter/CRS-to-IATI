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
 /* Spreadsheet columns for Denmark
  * 
  * Year
  * donorcode
  * donorname
  * agencycode
  * agencyname
  * crsid                         
  * projectnumber
  * initialreport
  * recipientcode
  * recipientname
  * regioncode
  * regionname
  * incomegroupcode
  * incomegroupname
  * flowcode
  * flowname
  * bi_multi
  * category
  * finance_t
  * aid_t
  * usd_commitment
  * usd_disbursement
  * usd_received
  * usd_commitment_defl
  * usd_disbursement_defl
  * usd_received_defl
  * usd_amountuntied
  * usd_amountpartialtied
  * usd_amounttied
  * usd_amountuntied_defl
  * usd_amountpartialtied_defl
  * usd_amounttied_defl
  * usd_irtc
  * usd_expert_commitment
  * usd_expert_extended
  * usd_export_credit
  * shortdescription
  * projecttitle
  * purposecode
  * purposename
  * sectorcode
  * sectorname
  * channelcode
  * channelname
  * channelreportedname
  * geography
  * expectedstartdate
  * completiondate
  * longdescription
  * gender
  * environment
  * trade
  * pdgg
  * FTC
  * sectorprogramme
  * investmentproject
  * assocfinance
  * biodiversity
  * climate
  * desertification
  * typerepayment
  * numberrepayment
  * interest1
  * interest2
  * repaydate1
  * repaydate2
  * grantelement
  * usd_interest
  * usd_outstanding
  * usd_arrears_principal
  * usd_arrears_interest
  * usd_future_DS_principal
  * usd_future_DS_interest
  */
?>

<?php
$geodata = array();
$missing_codes = array();
$region_codes_array = array(); //we'll use this to store a list of regions and countries represented in the data

//if csv file contains headers and you want to skip them call the script with
//php parse_csv.php headers=TRUE
$headers = $_SERVER['argv'][1];

$file = "denmark_test.csv";
$file = "denmark-5.csv";
$save_file = "xml/Denmark_";

$previous_activity_id = ""; //We need this to check that we have moved to a row with a new activity. If not we only want transaction data.

//Some default variables really more like constants
$default_lang = "en";
$default_currency = "USD";
$default_hierarchy =	"1";
$last_updated	= "2011-09-30T00:00:00";
$reporting_org_ref = "DK-1";
$reporting_org_type ="10";
$reporting_org_name = "Ministry of Foreign Affairs, Denmark";
$funding_org_ref = "DK";
$funding_org_type	= "10";
$funding_org_name = "Denmark";

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


//Parse the csv file and get the whole thing into a great big array
//if (($handle = fopen("allworld.csv", "r")) !== FALSE) {
if (($handle = fopen($file, "r")) !== FALSE) {
    //Ignore headers if set:
    if($headers == TRUE) {
      $row1 = fgetcsv($handle, 0, ';','"'); // read and ignore the first line
      //fgets($handle); // read and ignore the first line
      //print_r($row1);
      //die;
    } 
    while (($data = fgetcsv($handle, 0, ';','"')) !== FALSE) { //set string length parameter to 0 lets us pull in very long lines.
      foreach ($row1 as $key=>$value) {
        $this_row_to_array[$value] = utf8_encode($data[(int)$key]);
        //e.g. $crs_data['Year'] = utf8_encode($data[1])
        
        //Store region codes. We loop through them to generate separate files for each country
        if ($value == "recipientcode") {
          array_push($region_codes_array,utf8_encode($data[(int)$key]));
        }
      }   
      $crs_data[] = $this_row_to_array;
      $all_ids[] = $this_row_to_array['ActivityId'];
      //print_r($crs_data);  
      //die;        
    }
    fclose($handle);
}
//print_r(array_unique($region_codes_array)); die;
//print_r($codes);
//die;

//Get an array of unique activity ids
$all_ids = array_unique($all_ids);
//print_r($all_ids); die;

//link all rows of data with the same id together in an array
foreach ($all_ids as $id) {
  foreach ($crs_data as $row) {
    if ($row['ActivityId'] == $id) {
      $our_data[$id][] = $row;
      //print_r($our_data);die;
    }
  }
}
//print_r($our_data);die;
//Get the IATI Country code list into a usable array of "code"=>"name" format
$country_data = "Country_Codes_final.csv"; 
if (($country_handle = fopen($country_data, "r")) !== FALSE) {
    fgets($country_handle); // read and ignore the first line
    while (($country_data = fgetcsv($country_handle, 0, ',','"')) !== FALSE) {
      $countries[$country_data[0]] = array($country_data[1],$country_data[2]);
      //[DAC code] = array(ISO code, Name)
      //die;
    }
}
//print_r($countries);

//We're going to use these regions/countires to loop through the data and create an xml file for each 
//county/region. So first we need to tidy the array up a little
$region_codes_array = array_unique($region_codes_array);
//Just clear out any empty references. 
foreach ($region_codes_array as $key=>$value) {
  if (trim($value) == NULL) {
    unset($region_codes_array[$key]);
  }
}
//$region_codes_array = rsort($region_codes_array);
//print_r($region_codes_array); die;
//$region_codes_array = array("998","862");
foreach ($region_codes_array as $region_file) {
  
  // create a new XML document
  $doc = new DomDocument('1.0','UTF-8');
  //$doc = new DomDocument('1.0');
  //<iati-activities version="1.00" generated-datetime="datetime">
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

  //Find data for this region
  //Loop throu
  foreach ($our_data as $key => $data) {
    //echo $key . PHP_EOL;
    $data_keys = array_keys($data);
    $lastKey = end($data_keys);
    $last_array_item = $data[$lastKey];
    
    //print_r($last_array_item);die;
    if ($last_array_item["recipientcode"] == $region_file) {
      echo $region_file;
      
      
      include("main_activity_record.php");
      include("make_transactions.php");
      $flag = TRUE;
      $unset = $key;
      
    }
      /*Put all the data EXCEPT transaction data into xml
      then
      run through 
      for each $value as $transaction*/ 
  }
  
  if ($flag) {
    unset($our_data[$unset]);
    $flag = FALSE;
  }
  //Write the xml to a file
  $doc->formatOutput = true;
  //Give the file an ISO-2 code appendage
  if (isset($countries[$region_file][0]) && $countries[$region_file][0] != NULL) {
    $file_ref = $countries[$region_file][0]; //Two letter iso-2 code
  } else {
    $file_ref = $region_file; //region code - integer e.g. 998
  }
  
  $doc->save($save_file . $file_ref .'.xml');
  $xml_string = $doc->saveXML();
  echo $file_ref . PHP_EOL;

  //echo $xml_string;

  //print_r(array_unique($missing_codes));
  //print_r($missing_codes);
} //end for each country...
?>
