<?php
class apiRequest extends apiBase {

	public function __construct($arg=array()) {
		parent::__construct($arg);
	}

	public function output() {
		if (!$this->output)
			apiUtility::outputFailure('The API was unsuccessful in outputting.');

		apiUtility::outputSuccess($this->output);
	}

	public function process() {
		if ($this->requestType == "POST") {
			// Add matches to DB
			$this->output = apiDB::addMatch($this->params);
		} else {
			// Check params to see what data to return

			/* If no parameters, return top 10 teams */
			if (empty($this->params)) {
				$this->output = apiDB::top(array('limit' => 10));
			} else {
				if (isset($this->params['date_start']) && isset($this->params['date_end']) && isset($this->params['team'])) {
					// Return the team's record between the start and end dates
					$this->output = apiDB::teamDates($this->params);
				} else if (isset($this->params['date_start']) && isset($this->params['date_end'])) {
					// Return the top 10 teams between the start and end dates
					$this->output = apiDB::topDates($this->params);
				} else if (isset($this->params['team'])) {
					// Return the teams alltime record
					$this->output = apiDB::team($this->params);
				} else {
					// error
				}
			}
		}
	}

}