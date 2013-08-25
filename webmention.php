<?php

	namespace phpish\webmention;

	use phpish\http;
	use phpish\link_header;


	function discover($target_url)
	{
		$webmention_endpoint = NULL;
		$response_body = http\request("GET $target_url", array(), array(), $response_headers);
		if (isset($response_headers['link'])) {

			$links = link_header\parse($response_headers['link']);
			if (isset($links['http://webmention.org/'])) $webmention_endpoint = $links['http://webmention.org/'][0]['uri'];
			elseif (isset($links['webmention'])) $webmention_endpoint = $links['webmention'][0]['uri'];
		}
		if (!is_null($webmention_endpoint)) return $webmention_endpoint;
		elseif (preg_match('#<link href="([^"]+)" rel="http://webmention.org/" ?/?>#i', $response_body, $matches) or preg_match('#<link rel="http://webmention.org/" href="([^"]+)" ?/?>#i', $response_body, $matches))
		{
			return $matches[1];
		}
	}


	function send($source_url, $target_url)
	{
		if ($target_webmention_endpoint = discover($target_url))
		{
			$response_body = http\request("POST $target_webmention_endpoint", array(), array('source'=>$source_url, 'target'=>$target_url), $response_headers);
			return array('headers'=>$response_headers, 'body'=>$response_body);
		}
	}

?>