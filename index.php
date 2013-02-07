<?php
/*
 * Maze Solving script in PHP
 * Copyright Quinn Sakunaga
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright Quinn Sakunaga
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

class Maze {

  var $server = '';// Enter Server URI

  var $logging = 0;
  var $timeout = 90;
  var $userAgent = 'PHP';
  var $itemperpage = 50;
  var $runtime_start = 0;
  var $runtime_end= 0;
  var $unexploredCells = array();

  function __construct() {
    $this->main();
  }

  function out($message) {
    echo $message."\n";
  }

  function main() {
    $this->runtime_start = number_format(array_sum(explode(" ",microtime())),8,".","");
    $this->out($this->runtime_start);
    $this->initMaze();
    $this->runtime_end = number_format(array_sum(explode(" ",microtime())),8,".","");
    $this->out($this->runtime_end);
  }

  function initMaze() {
    $url = $this->server.'/api/init';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
    curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    $response = curl_exec($ch);
    $errorCode = curl_errno($ch);
    curl_close($ch);
    $response = json_decode($response,true);
    if($errorCode){
      $this->out('API errorCode: '.var_export($errorCode,true));
    }
    if(isset($response['currentCell'])){
      if(isset($response['currentCell']['mazeGuid'])){
        if(isset($response['currentCell']['note'])){
          $this->out(var_export($response['currentCell']['note'],true));
        }
        $this->exploreMaze($response['currentCell']['mazeGuid'],$response['currentCell']);
      }
    }else{
      $this->out('Could not get mazeGuid.');
    }
  }

  function exploreMaze($mazeGuid = '',$currentCell = array()) {
    if(($mazeGuid != '') 
      && isset($currentCell['x'])
      && isset($currentCell['y'])
      && isset($currentCell['north'])
      && isset($currentCell['east'])
      && isset($currentCell['south'])
      && isset($currentCell['west'])
    ){
      $url = '';
      $direction = strtoupper($this->findDerection($currentCell));
      if($direction != ''){
        $url = $this->server.'/api/move';
        $parameters = ''.
          'mazeGuid='.$mazeGuid.
          '&direction='.$direction
        ;
      }else{
        if(count($this->unexploredCells) > 0){
          $u = $this->unexploredCells[count($this->unexploredCells)-1];
        }else{
          $u = $this->unexploredCells[0];
        }
        if(isset($u['x']) && isset($u['y'])){
          $last = array_pop($this->unexploredCells);
          $url = $this->server.'/api/jump';
          $parameters = ''.
            'mazeGuid='.$mazeGuid.
            '&x='.$u['x'].
            '&y='.$u['y']
          ;
        }
      }
      if($url != ''){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
        $response = curl_exec($ch);
        $errorCode = curl_errno($ch);
        curl_close($ch);
        $response = json_decode($response,true);
        $this->out(var_export($response,true));
        if($errorCode){
          $this->out('API errorCode: '.var_export($errorCode,true));
        }
        if(isset($response['currentCell'])){
          if(isset($response['currentCell']['mazeGuid'])){
            if(isset($response['currentCell']['atEnd'])){
              if($response['currentCell']['atEnd'] == true){
                $this->out(var_export($response,true));
              }else{
                $this->exploreMaze($response['currentCell']['mazeGuid'],$response['currentCell']);
              }
            }
          }
        }else{
          $this->out('Could not get mazeGuid.');
        }
      }else{
        $this->out('Could not send a request. Something went wrong. This is the end of line.');
      }
    }
  }

  function findDerection($currentCell = array()){
    $direction = '';
    if( isset($currentCell['x'])
      && isset($currentCell['y'])
      && isset($currentCell['north'])
      && isset($currentCell['east'])
      && isset($currentCell['south'])
      && isset($currentCell['west'])
    ){
      $directions = array('north','east','south','west');
      $num = 0;
      foreach($directions as $d){
        if($currentCell[$d] == 'UNEXPLORED'){
          if($direction == ''){
            $direction = $d;
          }
          $num++;
        }
      }
      if($num > 1){
        $this->unexploredCells[] = $currentCell; 
      }
    }
    $this->out(count($this->unexploredCells));
    return $direction;
  }
}

$exec = new Maze();
exit();
