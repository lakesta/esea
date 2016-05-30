<?php
class apiDB {

	/* Database Params */
	public static $database = "esea";
	public static $name = "root";
	public static $host = "localhost";
	public static $pass = "root";
	public static $status = array('Win' => 1, 'Loss' => 2, 'Tie' => 3);

	/**
	 * Create a DB connection and return it or error out 
	 */
	protected static function connect() {
		$conn = mysqli_connect(apiDB::$host, apiDB::$name, apiDB::$pass, apiDB::$database);
		if (!$conn) {
			apiUtility::outputFailure();
		} else {
			return $conn;
		}
	}

	/** 
	 * GET Request with no parameters 
	 * Return top 10 teams by record
	*/
	public static function top($args = array()) {
		$db = apiDB::connect();
		$limit = $args['limit'];
		$q = <<<EOL
SELECT t.name team, SUM(mr.status=1) wins, SUM(mr.status=2) losses, SUM(mr.status=3) ties
FROM teams t
INNER JOIN match_results mr ON mr.team_id = t.id
GROUP BY t.id
ORDER BY wins DESC, losses ASC, ties ASC
LIMIT $limit;
EOL;
		$result = $db->query($q);
		$return = array();
		while ($row = $result->fetch_assoc()) {
			$return[] = $row;
		}
		$result->free();
		$db->close();
		return $return;
	}

	/**
	 * GET Request with Date Parameters 
	 * Return top 10 teams by record within the given date range
	 */
	public static function topDates($args = array()) {
		$db = apiDB::connect();
		$limit = $args['limit'];
		$start = date('Y-m-d H:i:s', strtotime($args['date_start']));
		$end = date('Y-m-d H:i:s', strtotime($args['date_end']));
		$q = <<<EOL
SELECT t.name team, SUM(mr.status=1) wins, SUM(mr.status=2) losses, SUM(mr.status=3) ties
FROM teams t
INNER JOIN match_results mr ON mr.team_id = t.id
WHERE mr.match_id IN (SELECT id FROM matches WHERE date >= '$start' AND date <= '$end')
GROUP BY t.id
ORDER BY wins DESC, losses ASC, ties ASC
LIMIT $limit;
EOL;
		$result = $db->query($q);
		$return = array();
		while ($row = $result->fetch_assoc()) {
			$return[] = $row;
		}
		$result->free();
		$db->close();
		return $return;
	}

	/**
	 * GET Request with Date and Team Parameters 
	 * Return team record within given date range
	 */
	public static function teamDates($args = array()) {
		$db = apiDB::connect();
		$team = $args['team'];
		$start = date('Y-m-d H:i:s', strtotime($args['date_start']));
		$end = date('Y-m-d H:i:s', strtotime($args['date_end']));
		$q = <<<EOL
SELECT t.name team, SUM(mr.status=1) wins, SUM(mr.status=2) losses, SUM(mr.status=3) ties
FROM teams t
INNER JOIN match_results mr ON mr.team_id = t.id
WHERE upper(t.name) = upper('$team')
AND mr.match_id IN (SELECT id FROM matches WHERE date >= '$start' AND date <= '$end')
GROUP BY t.id;
EOL;
		$result = $db->query($q);
		$return = array();
		while ($row = $result->fetch_assoc()) {
			$return[] = $row;
		}
		$result->free();
		$db->close();
		return $return;
	}

	/* 
	 * GET Request with Team Parameter 
	 * Return team record all time
	 */
	public static function team($args = array()) {
		$db = apiDB::connect();
		$team = $args['team'];
		$q = <<<EOL
SELECT t.name team, SUM(mr.status=1) wins, SUM(mr.status=2) losses, SUM(mr.status=3) ties
FROM teams t
INNER JOIN match_results mr ON mr.team_id = t.id
WHERE upper(t.name) = upper('$team')
GROUP BY t.id;
EOL;
		$result = $db->query($q);
		$return = array();
		while ($row = $result->fetch_assoc()) {
			$return[] = $row;
		}
		$result->free();
		$db->close();
		return $return;
	}

	/**
	 * POST request
	 * Insert match data into database (including teams and maps)
	 */
	public static function addMatch($data = array()) {
		set_time_limit(0);
		$db = apiDB::connect();

		if (!is_array($data) || !count($data)){
			return FALSE;
		}

		foreach($data as $match) {
			if (!isset($match['team1']) 
				|| !isset($match['team2']) 
				|| !isset($match['map']) 
				|| !isset($match['date']) 
				|| !isset($match['score1']) 
				|| !isset($match['score2'])) {
				continue;
			}
			// UPSERT TEAMS
			$q1 = $db->prepare("INSERT INTO teams (name) VALUES (?) ON DUPLICATE KEY UPDATE id=id");
			$q1->bind_param('s', $match['team1']);
			$q1->execute();
			$t1 = $db->insert_id;
			unset($q1);
			if (empty($t1)) {
				$q10 = $db->prepare("SELECT id FROM teams WHERE name = ?");
				$q10->bind_param('s', $match['team1']);
				$q10->execute();
				$q10->bind_result($t1);
				$q10->fetch();
				unset($q10);
			}
			
			$q2 = $db->prepare("INSERT INTO teams (name) VALUES (?) ON DUPLICATE KEY UPDATE id=id");
			$q2->bind_param('s', $match['team2']);
			$q2->execute();
			$t2 = $db->insert_id;
			unset($q2);
			if (empty($t2)) {
				$q20 = $db->prepare("SELECT id FROM teams WHERE name = ?");
				$q20->bind_param('s', $match['team2']);
				$q20->execute();
				$q20->bind_result($t2);
				$q20->fetch();
				unset($q20);
			}
			
			// UPSERT MAP
			$q3 = $db->prepare("INSERT INTO maps VALUES (NULL,?) ON DUPLICATE KEY UPDATE id=id");
			$q3->bind_param('s', $match['map']);
			$q3->execute();
			$map = $db->insert_id;
			unset($q3);
			if (empty($map)) {
				$q30 = $db->prepare("SELECT id FROM maps WHERE name = ?");
				$q30->bind_param('s', $match['map']);
				$q30->execute();
				$q30->bind_result($map);
				$q30->fetch();
				unset($q30);
			}

			// INSERT MATCH
			$date = date('Y-m-d H:i:s', strtotime($match['date']));
			$q4 = $db->prepare("INSERT INTO matches VALUES (NULL,?,?)");
			$q4->bind_param('ss', $date, $map);
			$q4->execute();
			$matchID = $db->insert_id;
			unset($q4);
			
			// INSERT MATCH RESULTS FOR BOTH TEAMS
			if ($match['score1'] > $match['score2']) {
				$t1s = apiDB::$status['Win'];
				$t2s = apiDB::$status['Loss'];
			} else if ($match['score1'] < $match['score2']) {
				$t1s = apiDB::$status['Loss'];
				$t2s = apiDB::$status['Win'];
			} else {
				$t1s = apiDB::$status['Tie'];
				$t2s = apiDB::$status['Tie'];
			}
			$q5 = $db->prepare("INSERT INTO match_results VALUES (?,?,?,?)");
			$q5->bind_param('iiii', $matchID, $t1, $match['score1'], $t1s);
			$q5->execute();
			unset($q5);

			$q6 = $db->prepare("INSERT INTO match_results VALUES (?,?,?,?)");
			$q6->bind_param('iiii', $matchID, $t2, $match['score2'], $t2s);
			$q6->execute();
			unset($q6);
		}

		$db->close();
		return TRUE;
	}
}