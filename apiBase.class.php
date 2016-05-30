<?php
abstract class apiBase {
	
	private $limit = 10;
	protected $params = array();
	protected $output = array();
	protected $requestType = 'GET';

	/*
	 * Object construct
	 */
	public function __construct() {
		if ($_SERVER['REQUEST_METHOD'] == "POST") {
			$this->params = apiUtility::getPost();
			$this->requestType = 'POST';
		}
		else {
			// If not a post request, we are going to check the query string
			$this->params = apiUtility::filterQueryString();
		}
		if (empty($this->params['limit'])) {
			$this->params['limit'] = $this->limit;
		}
		$this->process();
	}

	/*
	 * Output success or failure
	 */
	public function output() {
		if (!$this->output) {
			apiUtility::outputFailure();
		}

		apiUtility::outputSuccess($this->output);
	}
	

	/*
	 * Function child classes will use for primary functionality
	 */
	public abstract function process();

}