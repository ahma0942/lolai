<?php

use \RedBeanPHP\R as R;

class Lolai
{
  private GuzzleHttp\Client $http;
  private array $teams = [100 => 'BLUE', 200 => 'RED'];
  private array $roles = ['TOP' => 'TOP', 'JUNGLE' => 'JUNGLE', 'MIDDLE' => 'MID', 'BOTTOM' => 'ADC', 'UTILITY' => 'SUPPORT'];

  function __construct()
  {
    $this->http = new GuzzleHttp\Client([
      'base_uri' => 'https://europe.api.riotgames.com',
      'headers' => ['X-Riot-Token' => 'RGAPI-4cf54563-46f7-4eb1-81ec-5d51002a4398'],
      'http_errors' => false
    ]);
  }

  function get_summoner($ign)
  {
    list($name, $tag) = explode('#', $ign);
    $summoner = R::findOne('summoner', 'name = ? AND tag = ?', [$name, $tag]);
    if (!$summoner) {
      $req = $this->http->request('GET', "/riot/account/v1/accounts/by-riot-id/$name/$tag");
      if ($req->getStatusCode() == 429) {
        return 429;
      }
      $body = json_decode($req->getBody()->getContents(), true);

      $summoner = R::findOne('summoner', 'puuid = ?', [$body['puuid']]);
      if ($summoner) {
        $summoner->name = $name;
        $summoner->tag = $tag;
        R::store($summoner);
      } else {
        $summoner = R::dispense('summoner');
        $summoner->name = $name;
        $summoner->tag = $tag;
        $summoner->puuid = $body['puuid'];
        R::store($summoner);
      }
    }

    return $summoner;
  }

  function get_matches($puuid, $startTime = 0, $endTime = null)
  {
    if ($endTime == null) $endTime = time();
    $req = $this->http->request('GET', "/lol/match/v5/matches/by-puuid/$puuid/ids?startTime=$startTime&endTime=$endTime&queue=420&count=100");
    if ($req->getStatusCode() == 429) {
      return 429;
    }
    $match_keys = json_decode($req->getBody()->getContents(), true);
    $matches = [];
    foreach ($match_keys as &$match_key) {
      if (!R::findOne('match', 'match_key = ?', [$match_key])) {
        $match = R::dispense('match');
        $match->match_key = $match_key;
        $match->status = 'pending';
        $matches[] = $match;
      }
    }

    return $matches;
  }

  function get_match(&$match)
  {
    $req = $this->http->request('GET', "/lol/match/v5/matches/$match->match_key");
    if ($req->getStatusCode() == 429) {
      return 429;
    }

    $match_data = json_decode($req->getBody()->getContents(), true);
    if (!DI::mongodb()->lolai->matches->findOne(["metadata.matchId" => $match->match_key])) {
      DI::mongodb()->lolai->matches->insertOne($match_data);
    }
    foreach ($match_data['info']['participants'] as $participant) {
      $summoner = R::findOne('summoner', 'puuid = ?', [$participant['puuid']]);
      if (!$summoner) {
        $summoner = R::dispense('summoner');
        $summoner->name = $participant['riotIdGameName'];
        $summoner->tag = $participant['riotIdTagline'];
        $summoner->puuid = $participant['puuid'];
        R::store($summoner);
      }

      if (!in_array($participant['teamPosition'], ['TOP', 'JUNGLE', 'MIDDLE', 'BOTTOM', 'UTILITY'])) {
        $roles = ['TOP', 'JUNGLE', 'MIDDLE', 'BOTTOM', 'UTILITY'];
        foreach ($match_data['info']['participants'] as $checkP) {
          if ($checkP['teamId'] == $participant['teamId']) {
            $pos = array_search($checkP['teamPosition'], $roles);
            if ($pos !== false) {
              unset($hackers[$pos]);
            }
          }
        }
        if (count($roles) == 1) {
          $participant['teamPosition'] = $roles[0];
        } else {
          continue;
        }
      }

      $match->{$this->teams[$participant['teamId']] . '_' . $this->roles[$participant['teamPosition']]} = $summoner;
      $match->{$this->teams[$participant['teamId']] . '_' . $this->roles[$participant['teamPosition']] . '_CHAMPION'} = $participant['championId'];
    }
    $match->game_date = substr($match_data['info']['gameCreation'], 0, 10);
    $match->status = $match_data['info']['teams'][0]['win'] == true && $match_data['info']['teams'][0]['teamId'] == 100 ? 'BLUE_WIN' : 'RED_WIN';

    return 200;
  }
}
