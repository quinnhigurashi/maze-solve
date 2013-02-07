/*
 * Maze Solving script in JavaScript
 * Copyright Quinn Sakunaga
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright Quinn Sakunaga
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

var server = '';// Enter Server URI

$(document).ready(function(){
  var unexploredCells = [];
  function initMaze(){
    $.ajax({
      url: server+'/api/init',
      type: 'POST',
      success: function(data){
        if(data != undefined && data.currentCell != undefined && data.currentCell.mazeGuid != undefined){
          exploreMaze(data.currentCell.mazeGuid,data.currentCell);
        }
      },
      error: function(data){
        console.log(data);
      }
    });
  }
  function exploreMaze(mazeGuid,currentCell){
    var direction = findDirection(currentCell);
    var url, parameters,lastUnexploredCell;
    if(direction != ''){
      url = server+'/api/move';
      parameters = {
        mazeGuid: mazeGuid,
        direction: direction.toUpperCase()
      };
    }else{
      lastUnexploredCell = unexploredCells.pop();
      if((lastUnexploredCell.x.toString() != '') && (lastUnexploredCell.y.toString() != '')){
        url = server+'/api/jump';
        parameters = {
          mazeGuid: mazeGuid,
          x: lastUnexploredCell.x.toString(),
          y: lastUnexploredCell.y.toString()
        };
      }
    }
    if(url != ''){
      $.ajax({
        url: url,
        type: 'POST',
        data: parameters,
        success: function(data){
          if(data != undefined && data.currentCell != undefined && data.currentCell.mazeGuid != undefined){
            if(data.currentCell.atEnd == true){
              // Finished!
              console.log(data);
            }else{
              console.log('x: '+data.currentCell.x+', y: '+data.currentCell.y);
              exploreMaze(data.currentCell.mazeGuid,data.currentCell);
            }
          }
        },
        error: function(data){
          console.log(data);
        }
      });
    }
  }
  function findDirection(currentCell){
    var direction = '';
    var directions = ['north','east','south','west'];
    var numUnexplored = 0;
    for(var i=0;i<4;i++){
      if(currentCell[directions[i]] == 'UNEXPLORED'){
        if(direction == ''){
          direction = directions[i];
        }
        numUnexplored++; 
      }
    }
    if(numUnexplored > 1){
      unexploredCells.push(currentCell);
    }
    return direction;
  }
  initMaze();
});
