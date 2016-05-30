<?php
class apiUtility {

	/**
	 * Filter the query string and throw it into a clean array 
	 */
	static function filterQueryString() {
		$clean = array();
		foreach ($_GET as $key => $val) {
			$clean[$key] = htmlspecialchars_decode(filter_var($val, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW));
		}
		return $clean;
	}

	/**
	 * Output successful requests 
	 */
	static function outputSuccess($data = array()) {
		apiUtility::json_output($data);
	}

	/**
	 * If request fails, output error 
	 */
	static function outputFailure() {
		// return 500 error
		header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
		die();
	}

	/**
	 * Output JSON
	 */
	static function json_output($var = NULL) {
		// We are returning JSON, so tell the browser.
		header('Content-Type: application/json');

		if (isset($var)) {
			echo json_encode($var);
		}
	}

	/**
	 * Get POST data - assumption is that data is coming from cURL data source
	 */
	static function getPost() {
		$post = json_decode(file_get_contents('php://input'), true);
		return $post;
	}
}