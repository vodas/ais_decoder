<?php

//$aisMessage = '!AIVDM,1,1,,B,177KQJ5000G?tO`K>RA1wUbN0TKH,0*5C';
$aisMessage = 'AIVDM,1,1,,A,15MgK45P3@G?fl0E`JbR0OwT0@MS,0*4E';

$aisArray = explode(',',$aisMessage);
$id = substr($aisArray[0],0,3);
if ($id == '!AD') {
    $typeOfStation = 'MMEA 4.0 Dependent AIS Base Station';
    } else if ($id == '!AI') {
    $typeOfStation = 'Mobile AIS station';
    } else if ($id == '!AN') {
    $typeOfStation = 'NMEA 4.0 Aid to Navigation AIS station';
    } else if ($id == '!AR') {
    $typeOfStation = 'NMEA 4.0 AIS Receiving Station';
    } else if ($id == '!AS') {
    $typeOfStation = 'NMEA 4.0 Limited Base Station';
    } else if ($id == '!AT') {
    $typeOfStation = 'NMEA 4.0 AIS Transmitting Station';
    } else if ($id == '!AX') {
    $typeOfStation = 'NMEA 4.0 Repeater AIS station';
    } else if ($id == '!BS') {
    $typeOfStation = 'Base AIS station (deprecated in NMEA 4.0)';
    } else if ($id == '!SA') {
    $typeOfStation = 'NMEA 4.0 Physical Shore AIS Station';
    }
$countOfFragments = $aisArray[1];
$fragmentNumber = $aisArray[2];
$sequentialMessageId = $aisArray[3];
$radioChannel = $aisArray[4];
$payload = str_split($aisArray[5]);
$numberOfFillBits = substr($aisArray[6],0,1);
$checksum = substr($aisArray[6],2,4);

$ais_map64 = array(
    '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
    ':', ';', '<', '=', '>', '?', '@', 'A', 'B', 'C',
    'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
    'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W',
    '`', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i',
    'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's',
    't', 'u', 'v', 'w'
);

$binaryPayload = '';
foreach ($payload as $ascChar) {
    foreach ($ais_map64 as $key => $char) {
        if ($ascChar == $char) {
            $binaryPayload = $binaryPayload.sprintf( "%06d", decbin( $key ));

        }
    }
}

$message = array();
$message['type'] =  bindec(substr($binaryPayload,0,6));
$message['repeatIndicator'] = bindec(substr($binaryPayload,6,2));
$message['mmsi'] = bindec(substr($binaryPayload,8,30));
$message['navigationalStatus'] = bindec(substr($binaryPayload,38,4));
if ($message['navigationalStatus'] == 0) {
    $message['navigationalStatus'] = 'under way using engine';
} elseif($message['navigationalStatus'] == 1) {
    $message['navigationalStatus'] = 'at anchor';
} elseif($message['navigationalStatus'] == 2) {
    $message['navigationalStatus'] ='not under command';
} elseif($message['navigationalStatus'] == 3) {
    $message['navigationalStatus'] ='restricted maneuverability';
} elseif($message['navigationalStatus'] == 4) {
    $message['navigationalStatus'] ='constrained by her draught';
} elseif($message['navigationalStatus'] == 5) {
    $message['navigationalStatus'] ='moored';
} elseif($message['navigationalStatus'] == 6) {
    $message['navigationalStatus'] ='aground';
} elseif($message['navigationalStatus'] == 7) {
    $message['navigationalStatus'] = 'engaged in fishing';
} elseif($message['navigationalStatus'] == 8) {
    $message['navigationalStatus'] = ' under way sailing';
} elseif($message['navigationalStatus'] == 9) {
    $message['navigationalStatus'] = 'reserved for future amendment of navigational status for ships carrying DG, HS, or MP, or IMO hazard or pollutant category C, high speed craft (HSC)';
} elseif($message['navigationalStatus'] == 10) {
    $message['navigationalStatus'] = 'reserved for future amendment of navigational status for ships carrying dangerous goods (DG), harmful substances (HS) or marine pollutants (MP), or IMO hazard or pollutant category A, wing in ground (WIG)';
} elseif($message['navigationalStatus'] == 11) {
    $message['navigationalStatus'] ='power-driven vessel towing astern (regional use)';
} elseif($message['navigationalStatus'] == 12) {
    $message['navigationalStatus'] ='power-driven vessel pushing ahead or towing alongside (regional use)';
} elseif($message['navigationalStatus'] == 13) {
    $message['navigationalStatus'] ='reserved for future use';
} elseif($message['navigationalStatus'] == 14) {
    $message['navigationalStatus'] ='AIS-SART (active), MOB-AIS, EPIRB-AIS';
} elseif($message['navigationalStatus'] == 15) {
    $message['navigationalStatus'] ='undefined = default (also used by AIS-SART, MOB-AIS and EPIRB-AIS under test)';
}


if (substr($binaryPayload,42,1) == 0) {
    $message['rateOfTurn'] = bindec(substr($binaryPayload,43,7));
} elseif(substr($binaryPayload,42,1) == 1){
    $value='';
    for ($i=1;$i < 8;$i++) {
        if (substr($binaryPayload,42+$i,1) == 1) {
            $value = $value.'0';
        } else {
            $value = $value.'1';
        }
    }
    $message['rateOfTurn'] = -(bindec($value)+1);


}
if($message['rateOfTurn'] == 0) {
    $message['rateOfTurn'] = 'not turning';
} elseif ($message['rateOfTurn'] > 0 && $message['rateOfTurn'] < 127) {
    $message['rateOfTurn'] = ($message['rateOfTurn']/4.733)*($message['rateOfTurn']/4.733)."  turning right at up to 708 deg per min or higher";
} elseif ($message['rateOfTurn'] < 0 && $message['rateOfTurn'] > -127) {
    $message['rateOfTurn'] = ($message['rateOfTurn']/4.733)*($message['rateOfTurn']/4.733)."  turning left at up to 708 deg per min or higher";
} elseif ($message['rateOfTurn'] == 127) {
    $message['rateOfTurn'] = 'turning right at more than 5 deg per 30 s (No TI available)';
} elseif($message['rateOfTurn'] == -127) {
    $message['rateOfTurn'] = 'turning left at more than 5 deg per 30 s (No TI available)';
} elseif ($message['rateOfTurn'] == -128) {
    $message['rateOfTurn'] = 'indicates no turn information available (default)';
}

$message['speedOverGround'] = bindec(substr($binaryPayload,50,10))/10;
$message['positionAccuracy'] = substr($binaryPayload,60,1);
if ($message['positionAccuracy'] == 1) {
    $message['positionAccuracy'] = 'DGPS-quality fix with an accuracy of < 10ms';
} else {
    $message['positionAccuracy'] = 'an unaugmented GNSS fix with accuracy > 10m';
}
if (substr($binaryPayload,61,1) == 0) {
    $message['longitude'] = bindec(substr($binaryPayload, 62, 27))/600000;
} else {
    $value = '';
    for ($i = 0 ; $i < 27; $i++) {
        if (substr($binaryPayload,62+$i,1) == 1) {
            $value = $value.'0';
        } else {
            $value = $value.'1';
        }
    }

    $message['longitude'] = -(bindec($value)+1)/600000;
}

if (substr($binaryPayload,89,1) == 0) {
    $message['latitude'] = bindec(substr($binaryPayload, 90, 26))/600000;
} else {

}




var_dump($message);