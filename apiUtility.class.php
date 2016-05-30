<?php
class apiUtility {

	static function filterQueryString() {
		$clean = array();
		foreach ($_GET as $key => $val) {
			$clean[$key] = htmlspecialchars_decode(filter_var($val, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW));
		}
		return $clean;
	}

	static function outputSuccess($data = array()) {
		apiUtility::json_output($data);
	}

	static function outputFailure() {
		// return 500 error
	}

	static function json_output($var = NULL) {
		// We are returning JSON, so tell the browser.
		header('Content-Type: application/json');

		if (isset($var)) {
			echo json_encode($var);
		}
	}

	static function getPost() {
		$post = json_decode(file_get_contents('php://input'), true);
		return $post;
	}
}