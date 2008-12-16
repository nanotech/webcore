<?php
class Statistics
{
	public $logfile = 'access.log';

	public function add($phptime = false)
	{
		// Let's make sure the file exists and is writable first.
		if (is_writable($this->logfile)) {

			// In our example we're opening $this->logfile in append mode.
			// The file pointer is at the bottom of the file hence
			// that's where $somecontent will go when we fwrite() it.
			if (!$handle = fopen($this->logfile, 'a')) {
				echo "Cannot open file ($this->logfile)";
				exit;
			}

			//$referer = '';
			$referer = (isset($_SESSION['referer'])) ? 'REFERER: '.$_SESSION['referer'] : '';
			//$referer .= (isset($_SERVER['HTTP_REFERER'])) ? '"'.$_SERVER['HTTP_REFERER'].'"' : '';

			$line = 
				$_SERVER['REMOTE_ADDR'].
				' - ['.date('d/M/Y:H:i:s O').'] "'.
				$_SERVER['REQUEST_METHOD'].' '.
				$_SERVER['REQUEST_URI'].' '.
				$_SERVER['SERVER_PROTOCOL']."\"\n\t".
				$referer."\n\t".
				'HTTP_ACCEPT: '.$_SERVER['HTTP_ACCEPT']."\n\t".
				'HTTP_ACCEPT_CHARSET: '.$_SERVER['HTTP_ACCEPT_CHARSET']."\n\t".
				'HTTP_ACCEPT_ENCODING: '.$_SERVER['HTTP_ACCEPT_ENCODING']."\n\t".
				'HTTP_ACCEPT_LANGUAGE: '.$_SERVER['HTTP_ACCEPT_LANGUAGE']."\n\t".
				'USER_AGENT: '.$_SERVER['HTTP_USER_AGENT'];

			$line .= ($phptime != false) ? "\n\tPHP_TIME: $phptime" : '';

			// Write $somecontent to our opened file.
			if (fwrite($handle, $line."\n") === FALSE) {
				echo "Cannot write to file ($filename)";
				exit;
			}

			fclose($handle);

		} else return false;
	}
}
?>
