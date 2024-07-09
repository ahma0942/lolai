<?php

use \RedBeanPHP\R as R;

DI::rest()->get('/fix_dates', function () {
  $matches = DI::mongodb()->lolai->matches->find();
  $match_keys = [];
  foreach ($matches as $match) {
    $match_keys[] = $match['metadata']['matchId'];
  }

  $matches = R::findAll('match');
  foreach ($matches as $match) {
    if (!$match->game_date || !in_array($match->match_key, $match_keys)) {
      if (DI::lolai()->get_match($match) == 429) {
        http(429);
      }
      R::store($match);
    }
  }
});

DI::rest()->get('/lolai', function (RestData $data) {
  $lookups = R::getAll('SELECT id, blue_top_champion, blue_jungle_champion, blue_mid_champion, blue_adc_champion, blue_support_champion, red_top_champion, red_jungle_champion, red_mid_champion, red_adc_champion, red_support_champion, correct, algo, status FROM lookup ORDER BY id DESC');
  http(200, $lookups, true);
});

DI::rest()->post('/lolai/match', function (RestData $data) {
  $body = $data->request->getBody();

  $lookup = R::findOne('lookup', 'id = ?', [$body['id']]);
  $lookup->correct = $body['correct'];
  R::store($lookup);
  http(204);
});

DI::rest()->get('/lolai/cron', function (RestData $data) {
  $lookup = R::getRow("SELECT l.id, l.blue_top_champion, l.blue_jungle_champion, l.blue_mid_champion,
  l.blue_adc_champion, l.blue_support_champion, l.red_top_champion, l.red_jungle_champion,
  l.red_mid_champion, l.red_adc_champion, l.red_support_champion, l.before_date, s1.puuid as 'blue_top_puuid',
  s2.puuid as 'blue_jungle_puuid', s3.puuid as 'blue_mid_puuid', s4.puuid as 'blue_adc_puuid',
  s5.puuid as 'blue_support_puuid', s6.puuid as 'red_top_puuid', s7.puuid as 'red_jungle_puuid',
  s8.puuid as 'red_mid_puuid', s9.puuid as 'red_adc_puuid', s10.puuid as 'red_support_puuid' FROM lookup l
  LEFT JOIN summoner s1 ON l.blue_top_id = s1.id
  LEFT JOIN summoner s2 ON l.blue_jungle_id = s2.id
  LEFT JOIN summoner s3 ON l.blue_mid_id = s3.id
  LEFT JOIN summoner s4 ON l.blue_adc_id = s4.id
  LEFT JOIN summoner s5 ON l.blue_support_id = s5.id
  LEFT JOIN summoner s6 ON l.red_top_id = s6.id
  LEFT JOIN summoner s7 ON l.red_jungle_id = s7.id
  LEFT JOIN summoner s8 ON l.red_mid_id = s8.id
  LEFT JOIN summoner s9 ON l.red_adc_id = s9.id
  LEFT JOIN summoner s10 ON l.red_support_id = s10.id
  WHERE l.status = 'pending' OR l.status = 'processing'
  ORDER BY l.id LIMIT 1");

  if (!$lookup) {
    http(404);
  }

  $updateLookup = R::load('lookup', $lookup['id']);
  $updateLookup->status = 'processing';
  R::store($updateLookup);

  $time = $lookup['before_date'];
  $puuids = [];
  if ($lookup['blue_top_puuid']) $puuids[] = $lookup['blue_top_puuid'];
  if ($lookup['blue_jungle_puuid']) $puuids[] = $lookup['blue_jungle_puuid'];
  if ($lookup['blue_mid_puuid']) $puuids[] = $lookup['blue_mid_puuid'];
  if ($lookup['blue_adc_puuid']) $puuids[] = $lookup['blue_adc_puuid'];
  if ($lookup['blue_support_puuid']) $puuids[] = $lookup['blue_support_puuid'];
  if ($lookup['red_top_puuid']) $puuids[] = $lookup['red_top_puuid'];
  if ($lookup['red_jungle_puuid']) $puuids[] = $lookup['red_jungle_puuid'];
  if ($lookup['red_mid_puuid']) $puuids[] = $lookup['red_mid_puuid'];
  if ($lookup['red_adc_puuid']) $puuids[] = $lookup['red_adc_puuid'];
  if ($lookup['red_support_puuid']) $puuids[] = $lookup['red_support_puuid'];

  $summoners = R::findAll('summoner', 'puuid IN (' . R::genSlots($puuids) . ')', $puuids);
  $matches = [];
  foreach ($summoners as $summoner) {
    if (!$summoner->updated) {
      $matches = DI::lolai()->get_matches($summoner->puuid, 0, $time);
      if ($matches == 429) {
        http(429);
      }

      $summoner->updated = $time;
      R::store($summoner);
      R::storeAll($matches);
    } elseif ($summoner->updated < $time - 1) {
      $matches = DI::lolai()->get_matches($summoner->puuid, $summoner->updated, $time);
      if ($matches == 429) {
        http(429);
      }
      R::storeAll($matches);
    }
  }

  $matches = R::findAll('match', 'status = ?', ['pending']);
  foreach ($matches as $match) {
    if ($match->status == 'pending') {
      if (DI::lolai()->get_match($match) == 429) {
        http(429);
      }
      R::store($match);
    }
  }

  $ids = [];
  if ($updateLookup['blue_top_id']) $ids[] = $updateLookup['blue_top_id'];
  if ($updateLookup['blue_jungle_id']) $ids[] = $updateLookup['blue_jungle_id'];
  if ($updateLookup['blue_mid_id']) $ids[] = $updateLookup['blue_mid_id'];
  if ($updateLookup['blue_adc_id']) $ids[] = $updateLookup['blue_adc_id'];
  if ($updateLookup['blue_support_id']) $ids[] = $updateLookup['blue_support_id'];
  if ($updateLookup['red_top_id']) $ids[] = $updateLookup['red_top_id'];
  if ($updateLookup['red_jungle_id']) $ids[] = $updateLookup['red_jungle_id'];
  if ($updateLookup['red_mid_id']) $ids[] = $updateLookup['red_mid_id'];
  if ($updateLookup['red_adc_id']) $ids[] = $updateLookup['red_adc_id'];
  if ($updateLookup['red_support_id']) $ids[] = $updateLookup['red_support_id'];

  $matches = R::exportAll(R::findAll(
    'match',
    '(blue_top_id IN (' . R::genSlots($ids) . ') OR
  blue_jungle_id IN (' . R::genSlots($ids) . ') OR blue_mid_id IN (' . R::genSlots($ids) . ') OR
  blue_adc_id IN (' . R::genSlots($ids) . ') OR blue_support_id IN (' . R::genSlots($ids) . ') OR
  red_top_id IN (' . R::genSlots($ids) . ') OR red_jungle_id IN (' . R::genSlots($ids) . ') OR
  red_mid_id IN (' . R::genSlots($ids) . ') OR red_adc_id IN (' . R::genSlots($ids) . ') OR
  red_support_id IN (' . R::genSlots($ids) . ')) AND status != ?',
    array_merge($ids, $ids, $ids, $ids, $ids, $ids, $ids, $ids, $ids, $ids, ['pending'])
  ));

  if ($updateLookup->algo == 'NAIVE_BAYES') {
    $classifier = DI::ML()->naive_bayes($matches, $lookup);
    $updateLookup->status = $classifier->predict([
      $lookup['blue_top_champion'],
      $lookup['blue_jungle_champion'],
      $lookup['blue_mid_champion'],
      $lookup['blue_adc_champion'],
      $lookup['blue_support_champion'],
      $lookup['red_top_champion'],
      $lookup['red_jungle_champion'],
      $lookup['red_mid_champion'],
      $lookup['red_adc_champion'],
      $lookup['red_support_champion']
    ]);
  } else {
    $classifier = DI::ML()->knn($matches, $lookup);
    $updateLookup->status = $classifier->predict([
      $lookup['blue_top_champion'],
      $lookup['blue_jungle_champion'],
      $lookup['blue_mid_champion'],
      $lookup['blue_adc_champion'],
      $lookup['blue_support_champion'],
      $lookup['red_top_champion'],
      $lookup['red_jungle_champion'],
      $lookup['red_mid_champion'],
      $lookup['red_adc_champion'],
      $lookup['red_support_champion']
    ]);
    $updateLookup->algo = 'KNN';
  }

  R::store($updateLookup);
});

DI::rest()->post('/lolai', function (RestData $data) {
  $body = $data->request->getBody();

  $lookup = R::dispense('lookup');
  $lookup->status = 'pending';
  foreach ($body as $role => $player) {
    if ($player['IGN']) {
      $summoner = DI::lolai()->get_summoner($player['IGN']);
      if ($summoner == 429) {
        http(429);
      }
      $lookup->{$role} = $summoner;
    } else {
      $lookup->{$role . '_id'} = null;
    }
    $lookup->{$role . '_champion'} = $player['Champion'];
  }
  $lookup->before_date = $body['before_date'] ?? time();
  if ($body['algo']) {
    $lookup->algo = $body['algo'];
  }

  R::store($lookup);

  http(204);
});

DI::rest()->post('/lolai/rerun', function (RestData $data) {
  $body = $data->request->getBody();

  $find = R::findOne('lookup', 'id = ?', [$body['id']]);
  $lookup = R::dispense('lookup');
  $lookup->status = 'pending';
  $lookup->blue_top_id = $find->blue_top_id;
  $lookup->blue_jungle_id = $find->blue_jungle_id;
  $lookup->blue_mid_id = $find->blue_mid_id;
  $lookup->blue_adc_id = $find->blue_adc_id;
  $lookup->blue_support_id = $find->blue_support_id;
  $lookup->red_top_id = $find->red_top_id;
  $lookup->red_jungle_id = $find->red_jungle_id;
  $lookup->red_mid_id = $find->red_mid_id;
  $lookup->red_adc_id = $find->red_adc_id;
  $lookup->red_support_id = $find->red_support_id;
  $lookup->blue_top_champion = $find->blue_top_champion;
  $lookup->blue_jungle_champion = $find->blue_jungle_champion;
  $lookup->blue_mid_champion = $find->blue_mid_champion;
  $lookup->blue_adc_champion = $find->blue_adc_champion;
  $lookup->blue_support_champion = $find->blue_support_champion;
  $lookup->red_top_champion = $find->red_top_champion;
  $lookup->red_jungle_champion = $find->red_jungle_champion;
  $lookup->red_mid_champion = $find->red_mid_champion;
  $lookup->red_adc_champion = $find->red_adc_champion;
  $lookup->red_support_champion = $find->red_support_champion;
  $lookup->before_date = $find->before_date;
  $lookup->algo = $body['algo'] ?? $find->algo;
  R::store($lookup);

  http(204);
});
