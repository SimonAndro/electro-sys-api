<?php
function getFullUrl($queryStr = false)
{
    $request = $_SERVER;
    $host = (isset($request['HTTP_HOST'])) ? $request['HTTP_HOST'] : $request['SERVER_NAME'];
    $isSecure = (isset($request['HTTPS']) and $request['HTTPS'] == "on") ? true : false;
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $queryString = (isset($_SERVER['QUERY_STRING']) and $_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : null;
    $scheme = (config('https')) ? "https://" : "http://";
    $fullUrl = $scheme . $host . $uri;
    return $fullUrl = ($queryStr) ? $fullUrl . $queryString : $fullUrl;
}

function getQueryString()
{
    return (isset($_SERVER['QUERY_STRING']) and $_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : null;
}

function isSecure()
{
    return $isSecure = (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == "on") ? true : false;
}

function getScheme()
{
    return (config('https')) ? 'https' : 'http';
}

function getHost()
{
    $request = $_SERVER;
    $host = (isset($request['HTTP_HOST'])) ? $request['HTTP_HOST'] : $request['SERVER_NAME'];

    //remove unwanted characters
    $host = strtolower(preg_replace('/:\d+$/', '', trim($host)));
    //prevent Dos attack
    if ($host && '' !== preg_replace('/(?:^\[)?[a-zA-Z0-9-:\]_]+\.?/', '', $host)) {
        die();
    }

    return $host;
}

function server($name, $default = null)
{
    if (isset($_SERVER[$name])) {
        return $_SERVER[$name];
    }

    return $default;
}

function getRoot()
{
    $base = getBase();

    return getScheme() . '://' . getHost() . $base;
}

function getBase()
{
    $filename = basename(server('SCRIPT_FILENAME'));
    if (basename(server('SCRIPT_NAME')) == $filename) {
        $baseUrl = server('SCRIPT_NAME');
    } elseif (basename(server('PHP_SELF')) == $filename) {
        $baseUrl = server('PHP_SELF');
    } elseif (basename(server('ORIG_SCRIPT_NAME')) == $filename) {
        $baseUrl = server('ORIG_SCRIPT_NAME');
    } else {
        $baseUrl = server('SCRIPT_NAME');
    }

    $baseUrl = str_replace('index.php', '', $baseUrl);

    return $baseUrl;
}

/**
 * Function to get the request method
 * @return string
 */
function get_request_method()
{
    return strtoupper($_SERVER['REQUEST_METHOD']);
}

/**
 * Method to get path
 */
function path($path = "")
{
    $base = APP_BASE_PATH;
    return $base . $path;
}

function get_ip()
{
    //Just get the headers if we can or else use the SERVER global
    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
    } else {
        $headers = $_SERVER;
    }

    //Get the forwarded IP if it exists
    if (array_key_exists('X-Forwarded-For', $headers) && filter_var($headers['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $the_ip = $headers['X-Forwarded-For'];
    } elseif (array_key_exists('HTTP_X_FORWARDED_FOR', $headers) && filter_var($headers['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
    ) {
        $the_ip = $headers['HTTP_X_FORWARDED_FOR'];
    } else {
        $the_ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

    return $the_ip;
}

function url($url = '', $param = array(), $direct = false)
{
    return Request::instance()->url($url, $param, $direct);
}

function assetUrl($url = '')
{
    return Request::instance()->url($url, array(), true);
}

function get_file_extension($path)
{
    return strtolower(pathinfo($path, PATHINFO_EXTENSION));
}

function hash_check($content, $hash)
{
    return (md5($content) == $hash);
}

function hash_value($content)
{
    return md5($content);
}

function config($key, $default = null)
{
    return Request::instance()->config($key, $default);
}

function is_ajax()
{
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == "xmlhttprequest") {
        return true;
    }
    return false;
}

if (!function_exists('perfectSerialize')) {
    function perfectSerialize($string)
    {
        return base64_encode(serialize($string));
    }
}

if (!function_exists('perfectUnserialize')) {
    function perfectUnserialize($string)
    {

        if (base64_decode($string, true) == true) {

            return @unserialize(base64_decode($string));
        } else {
            return @unserialize($string);
        }
    }
}

function getController()
{
    return Request::instance()->controller;
}
function view($view, $param = array())
{
    return getController()->view($view, $param);
}
function model($model)
{
    return getController()->model($model);
}

function generateHash($u)
{
    $time = time();
    return md5(mt_rand(0, 9999) . $time . mt_rand(0, 9999) . $u . mt_rand(0, 9999));
}

function ipinfo()
{
    $client = empty($_SERVER['HTTP_CLIENT_IP'])
    ? null : $_SERVER['HTTP_CLIENT_IP'];
    $forward = empty($_SERVER['HTTP_X_FORWARDED_FOR'])
    ? null : $_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote = empty($_SERVER['REMOTE_ADDR'])
    ? null : $_SERVER['REMOTE_ADDR'];

    if (filter_var($client, FILTER_VALIDATE_IP)) {
        $ip = $client;
    } else if (filter_var($forward, FILTER_VALIDATE_IP)) {
        $ip = $forward;
    } else {
        $ip = $remote;
    }

    $res = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip), true);

    $ipinfo = [
        "request" => "", // Requestes Ip Address
        "status" => "", // Status code (200 for success)
        "credit" => "",
        "city" => "",
        "region" => "",
        "areaCode" => "",
        "dmaCode" => "",
        "countryCode" => "",
        "countryName" => "",
        "continentCode" => "",
        "latitude" => "",
        "longitude" => "",
        "regionCode" => "",
        "regionName" => "",
        "currencyCode" => "",
        "currencySymbol" => "",
        "currencySymbol_UTF8" => "",
        "currencyConverter" => "",
        "timezone" => "", // Will be used only in registration
        // process to detect user's
        // timezone automatically
        "neighbours" => [], // Neighbour country codes (ISO 3166-1 alpha-2)
        "languages" => [], // Spoken languages in the country
        // Will be user to auto-detect user language
    ];
    if (is_array($res)) {
        foreach ($res as $key => $value) {
            $key = explode("_", $key, 2);
            if (isset($key[1])) {
                $ipinfo[$key[1]] = $value;
            }
        }
    }

    if ($ipinfo["latitude"] && $ipinfo["longitude"]) {

        $username = config("geonamesorg-username");

        if ($username) {
            // Get timezone
            if (!empty($ipinfo["latitude"]) && !empty($ipinfo["longitude"])) {
                $res = @json_decode(file_get_contents("http://api.geonames.org/timezoneJSON?lat=" . $ipinfo["latitude"] . "&lng=" . $ipinfo["longitude"] . "&username=" . $username));

                if (isset($res->timezoneId)) {
                    $ipinfo["timezone"] = $res->timezoneId;
                }
            }

            // Get neighbours
            if (!empty($ipinfo["countryCode"])) {
                $res = @json_decode(file_get_contents("http://api.geonames.org/neighboursJSON?country=" . $ipinfo["countryCode"] . "&username=" . $username));

                if (!empty($res->geonames)) {
                    foreach ($res->geonames as $r) {
                        $ipinfo["neighbours"][] = $r->countryCode;
                    }
                }
            }

            // Get country
            if (!empty($ipinfo["countryCode"])) {
                $res = @json_decode(file_get_contents("http://api.geonames.org/countryInfoJSON?country=" . $ipinfo["countryCode"] . "&username=" . $username));

                if (!empty($res->geonames[0]->languages)) {
                    $langs = explode(",", $res->geonames[0]->languages);
                    foreach ($langs as $l) {
                        $ipinfo["languages"][] = $l;
                    }
                }
            }
        }
    }

    return json_decode(json_encode($ipinfo));
}

function autoLoadVendor()
{
    require_once path('app/vendor/autoload.php');
}

function mEncrypt($value)
{
    autoLoadVendor();
    try {
        $hash = Defuse\Crypto\Crypto::encrypt($value,
            Defuse\Crypto\Key::loadFromAsciiSafeString(config('crypto-key')));
        return $hash;
    } catch (Exception $e) {
        return $value;
    }
}

function mDcrypt($value)
{
    autoLoadVendor();
    try {
        $hash = Defuse\Crypto\Crypto::decrypt($value,
            Defuse\Crypto\Key::loadFromAsciiSafeString(config('crypto-key')));
        return $hash;
    } catch (Exception $e) {
        return $value;
    }
}

function isImage($source)
{
    $source = is_array($source) ? $source['name'] : $source;
    $name = pathinfo($source);
    $ext = isset($name['extension']) ? strtolower($name['extension']) : '';
    return in_array($ext, array('jpg', 'jpeg', 'png', 'gif'));
}

function isVideo($source)
{
    $source = is_array($source) ? $source['name'] : $source;
    $name = pathinfo($source);
    $ext = strtolower($name['extension']);
    return in_array($ext, array('mp4'));
}

function getHomeUrl()
{
    $url = url('content/library');
    return Hook::getInstance()->fire('user.home.url', $url, array($url));
}

function output_content($content)
{
    return $content;
}

function getimgsize($url, $referer = '')
{
    // Set headers
    $headers = array('Range: bytes=0-131072');
    if (!empty($referer)) {array_push($headers, 'Referer: ' . $referer);}

    // Get remote image
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    $data = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_errno = curl_errno($ch);
    curl_close($ch);

    // Get network stauts
    if ($http_status != 200) {
        echo 'HTTP Status[' . $http_status . '] Errno [' . $curl_errno . ']';
        return [0, 0];
    }

    // Process image
    $image = imagecreatefromstring($data);
    $dims = [imagesx($image), imagesy($image)];
    imagedestroy($image);

    return $dims;
}

function rand_string($length)
{
    /**
     * generates random string of given length
     */
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    return substr(str_shuffle($chars), 0, $length);
}

function dump_to_file() //debuging

{
    $args = func_get_args();

    if (file_exists("debug!.txt")) {
        foreach ($args as $things) {
            file_put_contents("debug!.txt",
                date("H:i:s") . "->" . print_r($things, true) . "\n", FILE_APPEND | LOCK_EX);
        }
    }

}
