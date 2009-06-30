<?php
/**
 * Prepends the BASE_URL to absolute urls (href="/something").
 */
class RewriteAbsoluteLinks extends Filter {

	public function parse($data)
	{
		global $config;

		preg_match_all('@(href|src|action)=("|\')(/(?:.*?))\\2@i', $data, $m);

		//
		// $m's contents are:
		//
		// 0: Entire matchs. Example: href="foo/bar.htm"
		// 1: Attributes.    Example: href
		// 2: Quotes.        Example: "
		// 3: URIs           Example: foo/bar.htm
		//

		$base_url = rtrim($config['base_url'], '/');

		if ($base_url === '') {
			$base_url = '/';
		}

		$hrefs  = $m[0];
		$attrs  = $m[1];
		$quotes = $m[2];
		$uris   = $m[3];

		$c = count($hrefs);

		for ($i=0; $i<$c; ++$i) {
			$old_href = $hrefs[$i];
			$new_uri = null;

			$uri     = $uris[$i];
			$uri_len = strlen($uri);
			$quote   = $quotes[$i];
			$attr    = $attrs[$i];

			if ($uri == '/') {
				$new_uri = $base_url.'/';
			}
			else if (
				$uri_len > 0
				// Ensure that the url isn't prefixed by the base url already.
				&& $uri !== $base_url
				&& strpos(rtrim($uri, '/'), $base_url) !== 0
			) {
				$new_uri = $base_url . $uri;
			}

			if ($new_uri) {
				$new_href = $attr . '=' . $quote . $new_uri . $quote;
				$data = str_replace($old_href, $new_href, $data);
			}
		}

		return $data;
	}
}
?>
