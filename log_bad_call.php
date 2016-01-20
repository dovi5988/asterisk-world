#!/usr/bin/php -q
<?php
include ("lib/phpagi/phpagi.php");

/*
This script requires the php-agi library (http://phpagi.sourceforge.net/) which is a bit old.

In features.conf under [applicationmap] you need to add:
log_call_caller => *#,self/caller,agi,/var/lib/asterisk/agi-bin/log_bad_call.php
log_call_callee => *#,self/callee,agi,/var/lib/asterisk/agi-bin/log_bad_call.php

Then in the dial plan you need to add in your dial plan something like:
Exten => _X.,1,set(__DYNAMIC_FEATURES=log_call_caller#log_call_callee)
*/

$email_contact = 'example@example.org'; //seperate them by ,

$agi = new AGI();

$sip_callid = get_var('SIPCALLID');

$peername = get_channel_var('peername');
$agi->verbose("WE NEED TO LOG CALL WITH SIP CALL ID $sip_callid FROM VOIPMONITOR AS PEER $peername IS COMPLAINING");


$values_to_get = array('audioreadformat', 'audionativeformat', 'audiowriteformat', 'peerip', 'recvip', 'from', 'uri', 'useragent', 'peername', 'peerip', 'peername', 'channeltype', 'rtpdest'); 
$email_msg = '';
$email_msg .= "Hi there,\n\n";
$email_msg .= "The peer $peername just dialed *# to indicate that they may have a quality issue. Below is the details of the call.\n\n\n"; 

foreach($values_to_get as $item){
        $var = get_channel_var($item);
        $email_msg .= "$item: $var\n";
        } 
$email_msg .= "PCAP URL: http://voipmonitor.example.org/?callid=$sip_callid\n";
$email_msg .= "\n\n\n";


//Output the message so we have it in our logs.
$agi->verbose($email_msg);

$parent_email = explode(',', $email_contact);
foreach($parent_email as $email){
        $subject = "Call quality complaint from $peername";
        $headers = 'From: no-reply@example.org' . "\r\n" .
	'Reply-To: no-reply@example.org' . "\r\n" .
    	'X-Mailer: PHP/' . phpversion();
        mail($email, $subject, $email_msg, $headers);
        }

function get_channel_var($var){
        global $agi;
        $chann_var_to_get = 'CHANNEL('.$var.')';
        $my_channel_var = $agi->get_variable($chann_var_to_get);
        $my_channel_var = $my_channel_var['data'];
        return $my_channel_var;
        }

function get_var($var){
        global $agi;
        $my_var = $agi->get_variable($var);
        $my_var = $my_var['data'];
        return $my_var;
        }

?>
