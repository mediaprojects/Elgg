<?php

/**
 * WARNING: API IN FLUX. DO NOT USE DIRECTLY.
 * 
 * Use the elgg_* versions instead.
 * 
 * @access private
 * 
 * @package Elgg.Core
 * @since   1.9.0
 */
class ElggLogger {
	/**
	 * The logging level. Determines how many of the logs get shown on-screen.
	 * Defaults to off (i.e., no logging).
	 * @var string
	 */
	private $level = '';
	
	
	/** @var ElggPluginHookService */
	private $hooks;
	
	/**
	 * Constructor
	 * 
	 * @param ElggPluginHookService $hooks Hooks service
	 */
	function __construct(ElggPluginHookService $hooks) {
		$this->hooks = $hooks;
	}
	
	
	/**
	 * @see elgg_log()
	 * @access private
	 */
	public function log($message, $level = 'NOTICE') {
		// only log when debugging is enabled
		if ($this->level) {
			// debug to screen or log?
			$to_screen = !($this->level == 'NOTICE');
	
			switch ($level) {
				case 'ERROR':
					// always report
					$this->dump("$level: $message", $to_screen, $level);
					break;
				case 'WARNING':
				case 'DEBUG':
					// report except if user wants only errors
					if ($this->level != 'ERROR') {
						$this->dump("$level: $message", $to_screen, $level);
					}
					break;
				case 'NOTICE':
				default:
					// only report when lowest level is desired
					if ($this->level == 'NOTICE') {
						$this->dump("$level: $message", FALSE, $level);
					}
					break;
			}
			
			// The message was logged
			return TRUE;
		}
	
		// Logging is disabled
		return FALSE;
	}
	
	
	/**
	 * @see elgg_dump()
	 * @access private
	 */
	public function dump($value, $to_screen = TRUE, $level = 'NOTICE') {
		global $CONFIG;
	
		// plugin can return false to stop the default logging method
		$params = array(
			'level' => $level,
			'msg' => $value,
			'to_screen' => $to_screen,
		);
		
		if (!$this->hooks->trigger('debug', 'log', $params, true)) {
			return;
		}
	
		// Do not want to write to screen before page creation has started.
		// This is not fool-proof but probably fixes 95% of the cases when logging
		// results in data sent to the browser before the page is begun.
		if (!isset($CONFIG->pagesetupdone)) {
			$to_screen = FALSE;
		}
	
		if ($to_screen == TRUE) {
			echo '<pre>';
			print_r($value);
			echo '</pre>';
		} else {
			error_log(print_r($value, TRUE));
		}
	}
	
	
	/**
	 * @access private
	 */
	function setLevel($level) {
		$this->level = $level;
	}
}