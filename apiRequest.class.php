<?php
class apiRequest extends apiBase {

	/**
	 * apiRequest constructor - nothing unique so just use apiBase's
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Process the API Request
	 */
	public function process() {
		if ($this->requestType == "POST") {
			// Add matches to DB
			$this->output = apiDB::addMatch($this->params);
		} else {
			// Check params to see what data to return
			// If no parameters, return top 10 teams
			if (empty($this->params)) {
				$this->output = apiDB::top(array('limit' => 10));
			} else {
				// Return the team's record between the start and end dates
				if (isset($this->params['date_start']) && isset($this->params['date_end']) && isset($this->params['team'])) {
					$this->output = apiDB::teamDates($this->params);
				}
				// Return the top 10 teams between the start and end dates 
				else if (isset($this->params['date_start']) && isset($this->params['date_end'])) {
					$this->output = apiDB::topDates($this->params);
				}
				// Return the teams all time record
				else if (isset($this->params['team'])) {
					$this->output = apiDB::team($this->params);
				}
			}
		}
	}
}