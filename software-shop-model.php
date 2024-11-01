<?php
/*** Software Shop functions ***/

/* get XML for categories */
require('sxml.php');
$sxml = new simplexml; 

function getXMLCategories($aff_id = 19393) {
    global $sxml;
    $data = readData("http://dc1datafeed.regnow.com/Srv/xs.aspx?req=3&gt=1&afid=$aff_id&cid=2&vid=-1");
    //$xml = simplexml_load_string($data);
    $xml = $sxml->xml_load_file($data);
    return $xml;
} 
/* get XML for specific category */        
function getXMLPrograms($catid = 1,$page = 1,$aff_id = 19393) {
    global $sxml;
    $data = readData("http://dc1datafeed.regnow.com/Srv/xs.aspx?req=1&pi=$page&afid=$aff_id&cid=$catid&vid=-1&kt=1&dk=&ps=20");
    //$xml = simplexml_load_string($data);
    $xml = $sxml->xml_load_file($data);
    return $xml;
}    
/* get XML for specific product */
function getXMLProgram($id,$aff_id = 19393) {
    global $sxml;
    $data = readData("http://dc1datafeed.regnow.com/Srv/xs.aspx?req=2&afid=$aff_id&cid=2&vid=-1&pid=$id");
    //$xml = simplexml_load_string($data);
    $xml = $sxml->xml_load_file($data);
    return $xml;
}

/* read categories */
function getCategories($xml) {
    $categories = Array();
    foreach ($xml->Category as $category) {
        foreach($category->attributes() as $key=>$val) { 
            $item[$key] = $val;
            if($key == 'Name') {
                $val = explode("::",$val);
                if(count($val) > 1) {
                    $item['category'] = $val[1]; 
                    $item['subcategory'] = $val[2];
                    array_push($categories,$item);
                }    
            }
        }
    }        
    return $categories;
}
/* read programs in specific category */
function getPrograms($xml) {
    $programs = Array();
    foreach ($xml->Product as $program) {
        foreach($program->attributes() as $key=>$val) { 
            $item[$key] = strip_tags($val);
        }
        array_push($programs,$item);
    }        
    return $programs;
}
/* read program */
function getProgram($xml) {
    foreach($xml->attributes() as $key=>$val) { 
      $item[$key] = strip_tags($val);
    }
    return $item;
}
/* get number of products */
function getProductsCount($xml) {
    foreach($xml->attributes() as $key=>$val) {
       if($key == 'TotalProducts') return $val;                
    }    
}
/* get number of pages */
function getPagesCount($products_count) {
    return ceil($products_count / 20);
}

/* get XML from regnow.com */
function readData($url) {
    $file_handle = @fopen($url, "r");
    if ($file_handle) {
        while (!feof($file_handle)) {
            $file_content .= fread($file_handle, 8192);
        }
        fclose($file_handle);        
    }
    else 
      // Use CURL
     if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5) Gecko/20041107 Firefox/1.0');
        $file_content = curl_exec($ch);
        curl_close($ch);
    }
    else 
    // Use fsockopen
    if(function_exists('fsockopen')) {
        $pu = parse_url($url); 
        $fp = fsockopen($pu['host'], 80, $errno, $errstr, 30);
        if (!$fp) {
            echo "$errstr ($errno)<br />\n";
        } else {
            $out = "GET ".$pu['path']."?".$pu['query']." HTTP/1.1\r\n";
            $out .= "Host: ".$pu['host']."\r\n";
            $out .= "Connection: Close\r\n\r\n";
            fwrite($fp, $out);
            while (!feof($fp)) {
                 $file_content .= fgets($fp, 128);
            }
            $start = strpos($file_content, '<?xml');
            $file_content = substr($file_content,$start);
            fclose($fp);
        }
    }   
    return $file_content;
}
?>
