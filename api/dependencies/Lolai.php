<?php
class Lolai
{
    private GuzzleHttp\Client $http;

    function __construct()
    {
        $this->http = new GuzzleHttp\Client(['base_uri' => 'https://europe.api.riotgames.com/', 'headers' => ['X-Riot-Token' => 'RGAPI-4a242e28-da6e-4174-9d5f-1141f23a697d']]);
    }

    function get_puuid($ign)
    {
        list($name, $tag) = explode('#', $ign);
        $summoner = R::findOne('summoner', 'name = ? AND tag = ?', [$name, $tag]);
        if (!$summoner) {
            $req = $this->http->request('GET', "riot/account/v1/accounts/by-riot-id/$name/$tag");
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

        return $summoner->puuid;
    }

    function get_matches($puuid, $since = 0) {
        $this->http->request('GET', "/lol/match/v5/matches/by-puuid/$puuid/ids?startTime=$since&queue=420&count=100");
    }
}
