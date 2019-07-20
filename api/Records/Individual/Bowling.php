<?php

class Bowling {
  
  const CAREER_BOWLING_BASE_SQL = "select p.playerid, p.scorecardName, mat.matches, "
			. "sum(bo.overs) as overs, (sum(bo.overs-floor(bo.overs))*10) as o_extra_balls, "
			. "sum(bo.maidens) as maidens, sum(bo.runs) as runs, "
			. "sum(bo.wickets) as wickets, sum(bo.runs)/sum(bo.wickets) as avg, "
			. "bb.wickets as bb_wickets, bb.runs bb_runs, count(if(bo.wickets>=5,1,null)) as 5I "
			. "from BOWLING bo natural join PLAYER p left join %s bb on p.playerid = bb.playerid ";
  
  const WILLOWFEST_BEST_BOWLING_TABLE = "(select * from " 
			. "(select * from ((select * from best_bowling_seasons where competitionid in " 
			. "(select competitionid from competition where associationNAme = 'Willowfest') "
			. "order by wickets desc, runs asc) as dfjv) group by playerid, bb.wickets, bb.runs) as dfgdg )";
  
  const WILLOWFEST_QUALIFIER_SQL = " and inningsid in (select inningsid from innings i inner join game g on i.gameid = g.gameid "
			. "inner join competition c on g.competitionid = c.competitionid where associationName = 'Willowfest') ";
  
  const MIN_WICKETS_FOR_AVERAGE = 10;
  
  const MAX_RESULTS = 10;
  
  private $db = null; 
  public function __construct($dbi) {
    $this->db = $dbi;
  }
  
  private function getCareerBowlingSql($wfOnly) {
    $sql = "";
    if ($wfOnly) {
      $sql .= sprintf(self::CAREER_BOWLING_BASE_SQL, self::WILLOWFEST_BEST_BOWLING_TABLE)
				. "left join (select b.playerid, count(*) as matches from BATTING b where 1=1" . WILLOWFEST_QUALIFIER_SQL . " group by b.playerId) mat on mat.playerid = p.playerid "
				. "where "  
        . " bb.competitionId in (select x.competitionid from COMPETITION x "
				. "where x.associationName = 'Willowfest') and ";
    } else {  
      $sql .= sprintf(self::CAREER_BOWLING_BASE_SQL, 'BEST_BOWLING');
			$sql .= "left join (select ba.playerid, count(*) as matches from BATTING ba group by ba.playerId) mat on mat.playerid = p.playerid "
				. "where ";
    }
    return $sql;
  }
  
  public function bestbowling($request, $response, $args) {
		$wfOnly = ($request->getQueryParam('willowfestOnly') === "true");
		
    $sql = "select b.bowlingId, p.playerId, g.gameId, gr.groundId, b.bowlingPos, b.inningsId, b.maidens, b.noBalls, b.overs, b.playerId, b.runs, b.wickets, b.wides, p.scorecardName, p.teamId, g.awayTeamId, (select teamName from team xi where xi.teamId = awayTeamId) as awayTeamName, g.competitionId, g.date, g.groundId, g.homeTeamId, (select teamName from team xi where xi.teamId = homeTeamId) as homeTeamName, g.round, gr.groundName " 
			. "from bowling b left outer join player p on b.playerId=p.playerId left outer join innings inning2_ on b.inningsId=inning2_.inningsId left outer join game g on inning2_.gameId=g.gameId left outer join ground gr on g.groundId=gr.groundId " 
			. "where p.teamId=2 " 
			. ($wfOnly ? " and competitionid in (select competitionid from competition where associationname = 'Willowfest') " : "")
			. "order by b.wickets desc, b.runs asc, b.overs asc limit " . MAX_RESULTS;
		if (!$result = $this->db->query(strtolower($sql))) {
      exit();
    }
    return json_encode($result->fetch_all(MYSQLI_ASSOC));
  }
  
  public function careerwickets($request, $response, $args) {
    $wfOnly = ($request->getQueryParam('willowfestOnly') === "true");
    $sql = $this->getCareerBowlingSql($wfOnly) . "p.teamid = 2 ";
		if ($wfOnly) {
      $sql .= self::WILLOWFEST_QUALIFIER_SQL;
    }
		$sql .= "group by p.playerid, bb.wickets, bb.runs order by wickets desc, runs asc, overs asc limit " . MAX_RESULTS;
		
		if (!$result = $this->db->query(strtolower($sql))) {
      exit();
    }
		
		$records = $this->processAggregateRecords($result->fetch_all(MYSQLI_ASSOC));
  	
    return json_encode($records);
  }
  
  public function careeraverage($request, $response, $args) {
    $wfOnly = ($request->getQueryParam('willowfestOnly') === "true");
    $sql = $this->getCareerBowlingSql($wfOnly) . "p.teamid = 2 ";
    if ($wfOnly) {
      $sql .= self::WILLOWFEST_QUALIFIER_SQL;
    }
    
    $sql .= "group by p.playerid, bb.wickets, bb.runs "
				. "having sum(bo.wickets) >= " . self::MIN_WICKETS_FOR_AVERAGE . " "
				. "order by (0 - sum(bo.runs)/sum(bo.wickets)) desc, wickets desc, overs asc limit " . MAX_RESULTS;
    
    if (!$result = $this->db->query(strtolower($sql))) {
      exit();
    }
		
		$records = $this->processAggregateRecords($result->fetch_all(MYSQLI_ASSOC));
  	
    return json_encode($records);
  }
	
	private function processAggregateRecords($records) {
		foreach ($records as &$r) {
			$overs = $r['overs'];
			$extraballs = $r['o_extra_balls'];
			// overs - sum of all part overs + number of completed overs in part overs + leftover balls from part overs
			$completeovers = $overs - ($extraballs/10) + intval($extraballs/6) + (($extraballs % 6)/10);
			$r['overs'] = $completeovers;
			$econ = $r['runs'] / (intval($completeovers) + (($extraballs % 6)/6));
		  $r['economy'] = $econ;
 		}
		return $records;
	}
}
?>
