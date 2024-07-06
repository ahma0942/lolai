<?php

use Phpml\Classification\KNearestNeighbors;

DI::rest()->post('/lolai', function (RestData $data) {
  $body = $data->request->getBody();

  $puuids = [
    $body['Ally']['Top']['IGN'] ? DI::lolai()->get_puuid($body['Ally']['Top']['IGN']) : null,
    $body['Ally']['Jungle']['IGN'] ? DI::lolai()->get_puuid($body['Ally']['Jungle']['IGN']) : null,
    $body['Ally']['Mid']['IGN'] ? DI::lolai()->get_puuid($body['Ally']['Mid']['IGN']) : null,
    $body['Ally']['Adc']['IGN'] ? DI::lolai()->get_puuid($body['Ally']['Adc']['IGN']) : null,
    $body['Ally']['Support']['IGN'] ? DI::lolai()->get_puuid($body['Ally']['Support']['IGN']) : null,
    $body['Enemy']['Top']['IGN'] ? DI::lolai()->get_puuid($body['Enemy']['Top']['IGN']) : null,
    $body['Enemy']['Jungle']['IGN'] ? DI::lolai()->get_puuid($body['Enemy']['Jungle']['IGN']) : null,
    $body['Enemy']['Mid']['IGN'] ? DI::lolai()->get_puuid($body['Enemy']['Mid']['IGN']) : null,
    $body['Enemy']['Adc']['IGN'] ? DI::lolai()->get_puuid($body['Enemy']['Adc']['IGN']) : null,
    $body['Enemy']['Support']['IGN'] ? DI::lolai()->get_puuid($body['Enemy']['Support']['IGN']) : null,
  ];
  http(200, $puuids, true);

  $samples = [[1, 3], [1, 4], [2, 4], [3, 1], [4, 1], [4, 2]];
  $labels = ['a', 'a', 'a', 'b', 'b', 'b'];

  $classifier = new KNearestNeighbors();
  $classifier->train($samples, $labels);

  http(200, $classifier->predict([3, 2]));
});
