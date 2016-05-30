<?php
abstract class apiBase {
	
	private $limit = 10;
	protected $params = array();
	protected $output = array();
	protected $requestType = 'GET';

	/*
	 * Object construct
	 * @params $arg array
	 *		params = an array of data passed in. used by function
	 *		decode_type = whether post is a json object or array
	 */
	public function __construct($arg) {
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
	 * Output with success or failure
	 * 
	 * This allows the api to be used by internal functions or
	 * as a standard api call eliminating need for a file_get_contents
	 * if an internal function wants to use the child api class
	 * 
	 * @param $exit bool
	 * 	Sets whether output should exit php process or not
	 * @return void
	 */
	public function output() {
		apiUtility::outputSuccess($this->output);
	}
	

	/*
	 * Function child classes are to use for primary functionality
	 */
	public abstract function process();

}