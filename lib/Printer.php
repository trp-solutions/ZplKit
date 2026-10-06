<?php
/*
ZplKit is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/HealDocument/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\ZplKit;

class Printer {
	public function __construct(private string $host, private int $port = 9100, private float $timeout = 5.0){
		if($host === '' || preg_match('/[\s\x00-\x1F\/\\\\]/', $host) || str_contains($host, '://')){
			throw new \InvalidArgumentException('Specify a printer hostname or IP address.');
		}
		if($port < 1 || $port > 65535 || !is_finite($timeout) || $timeout <= 0){
			throw new \InvalidArgumentException('Specify a valid port and positive finite timeout.');
		}
	}

	public function send(Label $label): void {
		$zpl = $label->render();
		$host = $this->host;
		if(str_contains($host, ':') && $host[0] !== '['){
			$host = '['.$host.']';
		}
		$stream = @stream_socket_client("tcp://$host:{$this->port}", $error_code, $error_message, $this->timeout);
		if($stream === false){
			throw new \RuntimeException("Unable to connect to printer: $error_message ($error_code).");
		}
		try {
			$deadline = microtime(true) + $this->timeout;
			$offset = 0;
			$length = strlen($zpl);
			while($offset < $length){
				$remaining = $deadline - microtime(true);
				if($remaining <= 0){
					throw new \RuntimeException('Printer write timed out; part of the label may have been sent.');
				}
				$seconds = (int) $remaining;
				$microseconds = max(1, (int) (($remaining - $seconds) * 1000000));
				stream_set_timeout($stream, $seconds, $microseconds);
				$written = @fwrite($stream, substr($zpl, $offset));
				if($written === false || $written === 0){
					throw new \RuntimeException('Printer write failed; part of the label may have been sent.');
				}
				$offset += $written;
			}
		} finally {
			fclose($stream);
		}
	}
}
