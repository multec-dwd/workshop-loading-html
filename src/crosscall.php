<?php
/*
------------------------------------------------------------------------------
The MIT License (MIT)

Copyright (c) 2014 jan.klaas.van.den.meersche@ehb.be

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
------------------------------------------------------------------------------
*/

if($_SERVER['SERVER_NAME'] != parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) ) exit('Ongeldige request.');//check if request is local
if(empty($_POST['url'])) exit('Ongeldige request. Verstuur een url mee via POST, bv: {"url":"http://www.example.com/"}');//check if url is passed via POST
$url = $_POST['url'];
if(!filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED )) exit('Ongeldige URL. Verstuur een geldige URL, bijvoorbeeld "http://www.example.com/"'); //check if valid url
if($_SERVER['SERVER_NAME'] == parse_url($url, PHP_URL_HOST) ) exit('Deze URL is niet toegelaten.'); //check if crossdomain call to prevent from reading local files

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec_follow($ch);
curl_close($ch);
echo utf8_decode($result);

//function curl_exec_follow based on the function with the same name by slopjong
function curl_exec_follow($ch, &$maxredirect = null)
{
  
	$user_agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_5) AppleWebKit/537.31 (KHTML, like Gecko) Chrome/26.0.1410.65 Safari/537.31";
	curl_setopt($ch, CURLOPT_USERAGENT, $user_agent );
	curl_setopt($ch, CURLOPT_ENCODING, '');

	if(!empty($_POST['postData']))
	{
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$_POST['postData']);
	}

  	$mr = $maxredirect === null ? 5 : intval($maxredirect);

  	if (ini_get('open_basedir') == '' && ini_get('safe_mode') == 'Off')
  	{

	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $mr > 0);
	    curl_setopt($ch, CURLOPT_MAXREDIRS, $mr);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

  	}
  	else
  	{
    
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

    	if ($mr > 0)
    	{
      		$original_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
      		$newurl = $original_url;
      
      		$rch = curl_copy_handle($ch);
      
		    curl_setopt($rch, CURLOPT_HEADER, true);
		    curl_setopt($rch, CURLOPT_NOBODY, true);
		    curl_setopt($rch, CURLOPT_FORBID_REUSE, false);
		    do
		    {
		        curl_setopt($rch, CURLOPT_URL, $newurl);
		        $header = curl_exec($rch);
		        if (curl_errno($rch))
		        {
		          	$code = 0;
		        }
		        else
		        {
	          		$code = curl_getinfo($rch, CURLINFO_HTTP_CODE);
	          		if ($code == 301 || $code == 302)
	          		{
	            		preg_match('/Location:(.*?)\n/', $header, $matches);
	            		$newurl = trim(array_pop($matches));
	            
	            		// if no scheme is present then the new url is a
			            // relative path and thus needs some extra care
			            if(!preg_match("/^https?:/i", $newurl))
			            {
			              $newurl = $original_url . $newurl;
			            }   
	          		}
	          		else
	          		{
	            		$code = 0;
	          		}
	        	}
      		} while ($code && --$mr);

      		curl_close($rch);
      
	      	if (!$mr)
	      	{
	        	if ($maxredirect === null)
	        		trigger_error('Too many redirects.', E_USER_WARNING);
	        	else
	        		$maxredirect = 0;
	        
	        	return false;
	      	}
	      	curl_setopt($ch, CURLOPT_URL, $newurl);
	    }
	}
  	return curl_exec($ch);
}
?>