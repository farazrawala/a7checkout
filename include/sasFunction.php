<?php

//get Client IP Address
function sas_get_client_ip()
{
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP')) $ipaddress = getenv('HTTP_CLIENT_IP');
    else if (getenv('HTTP_X_FORWARDED_FOR')) $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if (getenv('HTTP_X_FORWARDED')) $ipaddress = getenv('HTTP_X_FORWARDED');
    else if (getenv('HTTP_FORWARDED_FOR')) $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if (getenv('HTTP_FORWARDED')) $ipaddress = getenv('HTTP_FORWARDED');
    else if (getenv('REMOTE_ADDR')) $ipaddress = getenv('REMOTE_ADDR');
    else $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

function sas_get_region($ip)
{

    $curl = curl_init();
    if ($_SERVER['REMOTE_ADDR'] == '::1')
    {
        curl_setopt($curl, CURLOPT_URL, "https://ipinfo.io/203.135.30.178/json?token=12b59c8b5bf82e");
    }
    else
    {
        curl_setopt($curl, CURLOPT_URL, "https://ipinfo.io/" . $ip . "/json?token=12b59c8b5bf82e");
    }

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $ipResponse = curl_exec($curl);
    $ipResponse = (array)json_decode($ipResponse);
    $ipResponse['state'] = $ipResponse['region'];

    if (!empty($ipdat))
    {
        $region_detail = $ipResponse;
    }
    else
    {
        $region_detail = [];
    }

    return $region_detail;
}

function sas_getBrowser()
{
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $browser = "N/A";

    $browsers = ['/msie/i' => 'Internet explorer', '/firefox/i' => 'Firefox', '/safari/i' => 'Safari', '/chrome/i' => 'Chrome', '/edge/i' => 'Edge', '/opera/i' => 'Opera', '/mobile/i' => 'Mobile browser', ];

    foreach ($browsers as $regex => $value)
    {
        if (preg_match($regex, $user_agent))
        {
            $browser = $value;
        }
    }

    return $browser;
}

$regionDetail = sas_get_region(sas_get_client_ip());

$country = '';
$city = '';
$state = '';
$address = $city . ', ' . $country;

/// ------ End Code ----------------------------


///-------------------
function cxmEncrypt($string, $key)
{
    $result = '';
    for ($i = 0;$i < strlen($string);$i++)
    {
        $char = substr($string, $i, 1);
        $keychar = substr($key, ($i % strlen($key)) - 1, 1);
        $char = chr(ord($char) + ord($keychar));
        $result .= $char;
    }

    return base64_encode($result);
}

function cxmDecrypt($string, $key)
{
    $result = '';
    $string = base64_decode($string);

    for ($i = 0;$i < strlen($string);$i++)
    {
        $char = substr($string, $i, 1);
        $keychar = substr($key, ($i % strlen($key)) - 1, 1);
        $char = chr(ord($char) - ord($keychar));
        $result .= $char;
    }

    return $result;
}

$sasPrivateKey = "sas";

if ($_SERVER['REMOTE_ADDR'] == '::1') 
    $apiBaseUrl = "https://zekesix.com/api";
else 
    $apiBaseUrl = "https://zekesix.com/api";

?>
