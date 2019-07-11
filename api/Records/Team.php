<?php

class Team {
  
  const TEAM_RECORDS_BASE_SQL = "select i.inningsId, g.gameId, team2_.teamId, team3_.teamId, ground4_.groundId, i.byes, i.closureType, i.gameId, i.inningsOfMatch, i.leg_byes, i.no_balls, i.oversFaced, i.penalties, i.runsScored, i.teamId, i.wicketsLost, i.wides, g.awayTeamId, g.comment, g.competitionId, g.date, g.gameState, g.groundId, g.homeTeamId, g.reviewId, g.round, g.toss, g.winMargin, g.winTypeId, g.winner, team2_.teamName homeTeamName, team3_.teamName awayTeamName, ground4_.groundName from innings i left outer join game g on i.gameId=g.gameId left outer join team team2_ on g.homeTeamId=team2_.teamId left outer join team team3_ on g.awayTeamId=team3_.teamId left outer join ground ground4_ on g.groundId=ground4_.groundId ";
  
  const TEAM_RECORDS_FOR_SQL = self::TEAM_RECORDS_BASE_SQL . "where i.teamId = 2 ";
  const TEAM_RECORDS_AGAINST_SQL = self::TEAM_RECORDS_BASE_SQL . "where i.teamId != 2 ";
  
  private $db = null; 
  public function __construct($dbi) {
    $this->db = $dbi;
  }
  
  public function overallrecord() {
    $sql = " SELECT count(*) as matches, count(IF(winner = 2,1,null)) as won, count(IF((winner != 2) AND (winner != 0),1,null)) as lost, count(IF(winner = 0,1,null)) as noresult from GAME";
    if (!$result = $this->db->query(strtolower($sql))) {
      return ($this->db->error);
    }
    return json_encode($result->fetch_all(MYSQLI_ASSOC)[0]);
  }
  
  public function highestscoresfor() {
    $sql = self::TEAM_RECORDS_FOR_SQL . " order by i.runsScored desc limit " . MAX_RESULTS;
    if (!$result = $this->db->query(strtolower($sql))) {
      return ($this->db->error);
    }
    return json_encode($result->fetch_all(MYSQLI_ASSOC));
  }

  public function lowestscoresfor() {
    $sql = self::TEAM_RECORDS_FOR_SQL . " order by i.runsScored asc limit " . MAX_RESULTS;
    if (!$result = $this->db->query(strtolower($sql))) {
      return ($this->db->error);
    }
    return json_encode($result->fetch_all(MYSQLI_ASSOC));
  }
  
  public function highestscoresagainst() {
    $sql = self::TEAM_RECORDS_AGAINST_SQL . " order by i.runsScored desc limit " . MAX_RESULTS;
    if (!$result = $this->db->query(strtolower($sql))) {
      return ($this->db->error);
    }
    return json_encode($result->fetch_all(MYSQLI_ASSOC));
  }
  
  public function lowestscoresagainst() {
    $sql = self::TEAM_RECORDS_AGAINST_SQL . " order by i.runsScored asc limit " . MAX_RESULTS;
    if (!$result = $this->db->query(strtolower($sql))) {
      return ($this->db->error);
    }
    return json_encode($result->fetch_all(MYSQLI_ASSOC));
  }
}
?>