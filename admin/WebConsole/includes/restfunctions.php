<?php
 
/*
 * @function multiRequest.
 * @param    URLs array (may include header and POST data), cURL options array.
 * @return   Array of arrays with JSON requests and response codes.
 * @warning  Default options: does not verifying certificate, connection timeout 200 ms.
 * @Date     2015-10-14
 */
function multiRequest($data, $options=array(CURLOPT_SSL_VERIFYHOST => false, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_TIMEOUT_MS => 500)) {
 
  // array of curl handles
  $curly = array();
  // Data to be returned (response data and code)
  $result = array();
 
  // multi handle
  $mh = curl_multi_init();
 
  // loop through $data and create curl handles
  // then add them to the multi-handle
  foreach ($data as $id => $d) {
 

    $curly[$id] = curl_init();
 
    $url = (is_array($d) && !empty($d['url'])) ? $d['url'] : $d;
    curl_setopt($curly[$id], CURLOPT_URL, $url);
    // HTTP headers?
    if (is_array($d) && !empty($d['header'])) {
       curl_setopt($curly[$id], CURLOPT_HTTPHEADER, $d['header']);
    } else {
       curl_setopt($curly[$id], CURLOPT_HEADER, 0);
    }
    curl_setopt($curly[$id], CURLOPT_RETURNTRANSFER, 1);
 
    // post?
    if (is_array($d)) {
      if (!empty($d['post'])) {
        curl_setopt($curly[$id], CURLOPT_POST, 1);
        curl_setopt($curly[$id], CURLOPT_POSTFIELDS, $d['post']);
      }
    }

    // extra options?
    if (!empty($options)) {
      curl_setopt_array($curly[$id], $options);
    }
 
    curl_multi_add_handle($mh, $curly[$id]);
  }
 
  // execute the handles
  $running = null;
  do {
    curl_multi_exec($mh, $running);
  } while($running > 0);
 
 
  // Get content and HTTP code, and remove handles
  foreach($curly as $id => $c) {
    $result[$id]['data'] = curl_multi_getcontent($c);
    $result[$id]['code'] = curl_getinfo($c, CURLINFO_HTTP_CODE);
    curl_multi_remove_handle($mh, $c);
  }

 // all done
  curl_multi_close($mh);
 
  return $result;
}

