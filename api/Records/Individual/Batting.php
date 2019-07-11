<?php

class Batting {
	
	const CAREER_BATTING_BASE_SQL = "select p.playerid, p.scorecardname, count(b.battingid) as matches, "
			. "count(if(b.howoutid not in (0, 16, 17),1,null)) as innings, "
			. "count(if(b.howoutid in (1, 7, 13),1,null)) as notouts, "
			. "bb.score as bb_runs, bb.balls as bb_balls, bb.outstatus as bb_outstatus, "
			. "sum(b.score) as total, sum(b.balls) as ballsfaced, "
			. "count(if(b.score >= 50,1,null)) as 50s, "
   . "count(if(b.score = 0 AND b.howoutid not in (0,1,7,13,16,17),1,null)) as 0s, "
			. "count(if(b.score >= 100,1,null)) as 100s, "
			. "sum(b.score)/count(if(b.howoutid not in (0,1,7,13,16,17),1,null)) as avg, "
			. "(sum(if(b.balls > 0,b.score,0))/sum(b.balls))*100 as strikerate "
			. "from BATTING b natural join PLAYER p left join %s bb on p.playerId = bb.playerId "
			. "where ";
	
	const WILLOWFEST_BEST_BATTING_TABLE = "(select * from "
			. "(select * from ((select * from best_batting_seasons where competitionid in "
			. "(select competitionid from competition where associationName = 'Willowfest') " 
			. "order by score desc, balls asc) as dfjv) group by playerid) as dfgdg )";
	
	const MIN_INNINGS_FOR_AVERAGE = 10;
  
	private $db = null; 
  public function __construct($dbi) {
    $this->db = $dbi;
  }
  
  private function getCareerBattingSql($wfOnly) {
    $sql = "";
    if ($wfOnly) {
      $sql .= sprintf(self::CAREER_BATTING_BASE_SQL, self::WILLOWFEST_BEST_BATTING_TABLE)
        . " bb.competitionId in (select x.competitionid from COMPETITION x "
        . " where x.associationName = 'Willowfest') and ";
    } else {
      $sql .= sprintf(self::CAREER_BATTING_BASE_SQL, 'BEST_BATTING');
    }
    return $sql;
  }
  
  public function highestscore($request, $response, $args) {
    $wfOnly = ($request->getQueryParam('willowfestOnly') === "true");

    $sql = "select min(score) as score from (select b.score from BATTING b natural join PLAYER p "
			. " left join INNINGS i on b.inningsId = i.inningsId"
      . " where p.teamId = 2 " 
      . ($wfOnly ? WFONLY : "")
      . " order by b.score desc limit 10) as T";
    $result = $this->db->query(strtolower($sql));
    $score = $result->fetch_assoc()["score"];

    $sql = "select battingId, p.playerId, g.gameId, gr.groundId, balls, battingPos, fours, howOutId, i.inningsId, score, sixes, p.playerDetailId, playerName, scorecardName, status, p.teamId, awayTeamId, (select teamName from team xi where xi.teamId = awayTeamId) as awayTeamName, comment, competitionId, date, gameState, homeTeamId, (select teamName from team xi where xi.teamId = homeTeamId) as homeTeamName, reviewId, round, toss, winMargin, winTypeId, winner, groundName, mapRef, streetAddress, suburb " 
      . " from BATTING b left outer join PLAYER p on b.playerId=p.playerId left outer join INNINGS i on b.inningsId=i.inningsId left outer join GAME g on i.gameId=g.gameId left outer join GROUND gr on g.groundId=gr.groundId " 
      . " where b.score>=" . $score . " and p.teamId=2 " 
      . ($wfOnly ? " and competitionid in (select competitionid from competition where associationname = 'Willowfest') " : "")
      . " order by b.score desc, b.balls asc, g.date asc";
    if (!$result = $this->db->query(strtolower($sql))) {
      exit();
    }
    return json_encode($result->fetch_all(MYSQLI_ASSOC));
  }
  
  public function careerruns($request, $response, $args) {
    $wfOnly = ($request->getQueryParam('willowfestOnly') === "true");
    $sql = $this->getCareerBattingSql($wfOnly) . "p.teamid = 2 ";
    if ($wfOnly) {
      $sql .= WILLOWFEST_QUALIFIER_SQL;
    }
    $sql .= " group by playerid order by total desc, avg desc, strikerate desc, ballsfaced asc limit " . MAX_RESULTS;

    if (!$result = $this->db->query(strtolower($sql))) {
      exit();
    }
    return json_encode($result->fetch_all(MYSQLI_ASSOC));
  }
  
  public function careeraverage($request, $response, $args) {
    $wfOnly = ($request->getQueryParam('willowfestOnly') === "true");
    $sql = $this->getCareerBattingSql($wfOnly) . "p.teamid = 2 ";
    if ($wfOnly) {
      $sql .= WILLOWFEST_QUALIFIER_SQL;
    }
    $sql .= " group by playerid "
				. " having count(if(b.howoutid not in (0, 16, 17),1,null)) >= "
				. self::MIN_INNINGS_FOR_AVERAGE 
				. " order by avg desc, total desc, strikerate desc, ballsfaced asc limit " . MAX_RESULTS;
    
    if (!$result = $this->db->query(strtolower($sql))) {
      exit();
    }
    return json_encode($result->fetch_all(MYSQLI_ASSOC));
  }
}
?>