<?php
class MailPlugin {

	private $host;
	private $port;
	private $username;
	private $password;

	public $from;

	public function MailPlugin($host, $port, $username, $password) {
		$this->host = $host;
		$this->port = $port;
		$this->username = $username;
		$this->password = $password;
		$this->from = $this->username;
	}

	public function send($to, $subject, $body, $from = '') {
		require_once 'Mail.php';
		require_once 'Mail/mime.php';
		$mime = new Mail_mime();

		if(empty($from)) $from = $this->from;

		$headers = array ('From' => $from,
			'To' => $to,
			'Subject' => $subject);

		$smtp = Mail::factory('smtp',
			array ('host' => $this->host,
			'port' => $this->port,
			'auth' => true,
			'username' => $this->username,
			'password' => $this->password));

		$mime->setHTMLBody($body);
		$body = $mime->get();
		$headers = $mime->headers($headers);

		$mail = $smtp->send($to, $headers, $body);

		if (PEAR::isError($mail)) {
			return $mail->getMessage();
		} else {
			return true;
		}
	}

	/*
	 * from http://www.linuxjournal.com/article/9585
	 */
	function checkEmail($email)
	{
		$isValid = true;
		$atIndex = strrpos($email, '@');
		if (is_bool($atIndex) && !$atIndex)
		{
			$isValid = false;
		}
		else
		{
			$domain = substr($email, $atIndex+1);
			$local = substr($email, 0, $atIndex);
			$localLen = strlen($local);
			$domainLen = strlen($domain);
			if ($localLen < 1 || $localLen > 64)
			{
				// local part length exceeded
				$isValid = false;
			}
			else if ($domainLen < 1 || $domainLen > 255)
			{
				// domain part length exceeded
				$isValid = false;
			}
			else if ($local[0] == '.' || $local[$localLen-1] == '.')
			{
				// local part starts or ends with '.'
				$isValid = false;
			}
			else if (preg_match('/\\.\\./', $local))
			{
				// local part has two consecutive dots
				$isValid = false;
			}
			else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
			{
				// character not valid in domain part
				$isValid = false;
			}
			else if (preg_match('/\\.\\./', $domain))
			{
				// domain part has two consecutive dots
				$isValid = false;
			}
			else if
				(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
				str_replace("\\\\",'',$local)))
			{
				// character not valid in local part unless 
				// local part is quoted
				if (!preg_match('/^"(\\\\"|[^"])+"$/',
					str_replace("\\\\",'',$local)))
				{
					$isValid = false;
				}
			}
			/*
			if ($isValid && !(checkdnsrr($domain,'MX') || checkdnsrr($domain,'A')))
			{
				// domain not found in DNS
				$isValid = false;
			}
			 */
		}
		return $isValid;
	}

}
