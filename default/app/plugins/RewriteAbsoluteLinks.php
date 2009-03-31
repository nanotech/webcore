<?php
/**
 * Prepends the BASE_URL to absolute urls (href="/something").
 */
class RewriteAbsoluteLinks extends Filter {
	static public $type = 'a -> b';

	public function parse($data)
	{
		preg_match_all('@(href|src|action)=("|\')/(.*?)\\2@i', $data, $m);

		$c = count($m[0]);
		for ($i=0;$i<$c;++$i) {
			$old_href = $m[0][$i];

			if (strlen($m[3][$i]) > 0 && strstr(trim($m[3][$i], '/'), trim(BASE_URL, '/')) === false) {
				$quote = $m[2][$i]; # " or '
				$attr = $m[1][$i];  # href
				$slash = ($m[3][$i]{0} == '/') ? '' : '/';
				$new_href = $attr.'='.$quote.BASE_URL.$slash.$m[3][$i].$quote;
				$data = str_replace($old_href, $new_href, $data);
			}
		}

		return $data;
	}
}
?>
