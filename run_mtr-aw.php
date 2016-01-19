#!/usr/bin/php -q
<?php
include ("lib/phpagi/phpagi.php");

// This script requires the php-agi library (http://phpagi.sourceforge.net/) which is a bit old.
$email_contact = 'email@example.org'; //seperate them by ,

$agi = new AGI();

//This line is not needed but hepful if you want to dump all the data on the call.
//$agi->exec("DumpChan", "");


$agi->exec('PlayBack', 'one-moment-please');

$values_to_get = array('audioreadformat', 'audionativeformat', 'audiowriteformat', 'peerip', 'recvip', 'from', 'uri', 'useragent', 'peername', 'peerip', 'peername', 'channeltype', 'rtpdest'); 

$peer = get_channel_var('peername');

$email_msg = '';
$email_msg .= "Hi there,\n\n";
$email_msg .= "The peer $peer just dialed *222 to indicate that they may have a quality issue. Below is the details of the connection as well as a trace.\n\n\n"; 

foreach($values_to_get as $item){
        $var = get_channel_var($item);
        $email_msg .= "$item: $var\n";
        } 
$email_msg .= "\n\n\n";

$rtp_ip = get_channel_var('rtpdest');
$rtp_ip = explode(':', $rtp_ip);
$rtp_ip = $rtp_ip[0];

$output = shell_exec("/usr/sbin/mtr -o \"L SRD NBAW JMXI\" --report --report-cycles 5 --no-dns $rtp_ip");
$email_msg .= $output;

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

$agi->exec('PlayBack', 'privacy-thankyou');

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
