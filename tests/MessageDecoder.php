<?php

use SpringDvs\Message;


class MessageDecoder {
	
	/**
	 * Extract spring message byte response into the service text
	 * @param string $str
	 */
	public static function extractServiceText($str) {
		return self::decodeMessage($str)->getContentResponse()->getServiceText()->get();
	}
	
	/**
	 * Extract and decode service text as JSON object
	 * @param string $str
	 * @return string[]
	 */
	public static function jsonServiceText($str) {
		return json_decode(self::extractServiceText($str),true);
	}
	

	public static function jsonServiceTextStripNode($str) {
		$v =  json_decode(self::extractServiceText($str), true);
		return reset($v);
	}
	/**
	 * Decode byte sequence into message
	 * @param unknown $str
	 * @return SpringDvs\Message
	 */
	public static function decodeMessage($str) {
		return Message::fromStr($str);
	}
}