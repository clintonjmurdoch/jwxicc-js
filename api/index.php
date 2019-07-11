<?php
require 'vendor/autoload.php';
require_once 'mysql.php';
require_once 'Records/Individual/Batting.php';
require_once 'Records/Individual/Bowling.php';
require_once 'Records/Team.php';
$app = new \Slim\App();

$app->add(function($request,$response,$next) {
  $response = $next($request,$response);
  return $response->withHeader('Content-Type', 'application/json');
});

define('MIN_PSHIP',20);
define('MAX_RESULTS',10);
define('WFONLY'," and i.inningsid in (select i.inningsid from innings i left join game g on i.gameid=g.gameid left join competition c on c.competitionid=g.competitionid where c.associationName = 'Willowfest')");
define('WILLOWFEST_QUALIFIER_SQL', " and b.inningsid in (select inningsid from innings i inner join game g on i.gameid = g.gameid "
			. "inner join competition c on g.competitionid = c.competitionid where associationName = 'Willowfest') ");

$container = $app->getContainer();
$container['db'] = function () {
  //$mysqli = new mysqli("sql211.epizy.com", "epiz_20874118", "WzhK6iCB7D", "epiz_20874118_jsf");
  //$mysqli = new mysqli("jwxicc.czg4gbfnlvfj.us-west-2.rds.amazonaws.com", "jwxicc", "jwxiccmysql", "jwxicc");
  $mysqli = new mysqli("localhost", "root", null, "epiz_20874118_jsf", 3306);
  return $mysqli;
};

$container['Batting'] = function($c) {
    return new Batting($c->get("db"));
};
$container['Bowling'] = function($c) {
    return new Bowling($c->get("db"));
};
$container['Team'] = function($c) {
    return new Team($c->get("db"));
};

$app->get('/player/{playerId}', function ($request, $response, $args) {
  $playerId = $args['playerId'];
  $sql = "select * from player where playerId = $playerId";
  $result = $this->db->query(strtolower($sql));
  echo json_encode($result->fetch_assoc());
});

// Team Records
$app->get('/records/team/overallrecord', 'Team:overallrecord');
$app->get('/records/team/highestscores/for', 'Team:highestscoresfor');
$app->get('/records/team/lowestscores/for', 'Team:lowestscoresfor');
$app->get('/records/team/highestscores/against', 'Team:highestscoresagainst');
$app->get('/records/team/lowestscores/against', 'Team:lowestscoresagainst');

// Batting Records
$app->get('/records/individual/batting/highscore', 'Batting:highestscore');
$app->get('/records/individual/batting/careerruns', 'Batting:careerruns');
$app->get('/records/individual/batting/careeraverage', 'Batting:careeraverage');

// Bowling Records
$app->get('/records/individual/bowling/bestbowling', 'Bowling:bestbowling');
$app->get('/records/individual/bowling/careerwickets', 'Bowling:careerwickets');
$app->get('/records/individual/bowling/careeraverage', 'Bowling:careeraverage');

// Partnerships
$app->get('/records/partnerships/{wicket}', function ($request, $response, $args) {
  $wicket = (int)$args['wicket'];
	$wfOnly = ($request->getQueryParam('willowfestOnly') === "true");
  $sql = "select min(score) as score from (select ps.runsScored as score from PARTNERSHIP ps inner join INNINGS i on ps.inningsId = i.inningsId where i.teamId = 2" . ($wicket > 0 ? " and ps.wicket=" . $wicket : "") . ($wfOnly ? WFONLY : "") . " order by ps.runsScored desc limit 10) as T";
  $result = $this->db->query(strtolower($sql));
  $score = $result->fetch_assoc()['score'];
  $score = ($score < MIN_PSHIP ? MIN_PSHIP : $score);

  $sql = "select i.gameid, i.teamid, g.date, gr.groundname, g.hometeamid, (select teamName from team xi where xi.teamId = g.homeTeamId) as homeTeamName, g.awayteamid, (select teamName from team xi where xi.teamId = g.awayTeamId) as awayTeamName, partnershi0_.partnershipId, partnershi0_.inningsId, partnershi0_.overs, partnershi0_.oversAtEnd, partnershi0_.runsScored, partnershi0_.scoreAtEnd, partnershi0_.wicket " 
    . " from partnership partnershi0_ inner join innings i on partnershi0_.inningsId=i.inningsId left outer join game g on i.gameId = g.gameId inner join ground gr on g.groundid=gr.groundid" 
    . " where i.teamId=2 and partnershi0_.runsScored>=" . $score . ($wicket > 0 ? " and partnershi0_.wicket=" . $wicket : "") 
		. ($wfOnly ? WFONLY : "")
    . " order by partnershi0_.runsScored desc";
  if (!$result = $this->db->query(strtolower($sql))) {
    exit();
  }
  $pships = $result->fetch_all(MYSQLI_ASSOC);
  foreach ($pships as &$p) {
    $sql = "select partnershi0_.partnershipId, partnershi0_.partnershipPlayerId, partnershi0_.battingPosition, partnershi0_.contribution, partnershi0_.outStatus, partnershi0_.partnershipId, partnershi0_.playerId, partnershi0_.retiredNotOutStatus, player1_.playerId as pl_playerid, player1_.playerDetailId, player1_.playerName, player1_.scorecardName, player1_.status, player1_.teamId, team2_.teamId as teamId1_15_3_, team2_.teamName as teamName2_15_3_ from partnership_player partnershi0_ inner join player player1_ on partnershi0_.playerId=player1_.playerId inner join team team2_ on player1_.teamId=team2_.teamId where partnershi0_.partnershipId= " . $p['partnershipid'] . " order by partnershi0_.battingPosition asc";
    $result = $this->db->query(strtolower($sql));
    $p['players'] = $result->fetch_all(MYSQLI_ASSOC);
  }
  echo json_encode($pships);
});

$app->run();

?>