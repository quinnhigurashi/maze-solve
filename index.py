#!/usr/bin/env python

# Maze Solving script in Python
# Copyright Quinn Sakunaga
#
# Licensed under The MIT License
# Redistributions of files must retain the above copyright notice.
#
# @copyright     Copyright Quinn Sakunaga
# @license       MIT License (http://www.opensource.org/licenses/mit-license.php)

import httplib, urllib, json, sys

sys.setrecursionlimit(1000000)

server = '';# Enter Server URI

unexplored = []
headers = {"Content-type": "application/x-www-form-urlencoded","Accept": "text/plain"}
conn = httplib.HTTPConnection(server, 80, timeout=14)
directions = ['north','east','south','west']

def index():
  conn.request("POST", '/api/init', None, headers)
  response = conn.getresponse()
  data = json.loads(response.read())
  conn.close()
  print data['currentCell']['mazeGuid']
  if data['currentCell']['mazeGuid']:
    explore(data['currentCell']['mazeGuid'],data['currentCell']);
  return

def explore(mazeGuid,currentCell):
  global unexplored
  url = ''
  direction = findDirection(currentCell)
  print direction
  if direction != '':
    url = '/api/move'
    params = urllib.urlencode({'mazeGuid': mazeGuid, 'direction': direction.upper() })
  else:
    if len(unexplored) > 0:
      _last = unexplored.pop()
      url = '/api/jump'
      params = urllib.urlencode({'mazeGuid': mazeGuid, 'x': _last['x'], 'y': _last['y']  })

  if url != '':
    conn.request("POST", url, params, headers)
    response = conn.getresponse()
    if response.status == 200:
      _d = response.read()
      data = json.loads(_d)
      if data['currentCell']['mazeGuid']:
        if data['currentCell']['atEnd'] == True:
          print data
          print 'Congratulation!'
        else:
          print '---'
          print data['currentCell']['x']
          print data['currentCell']['y']
          print '---'
          explore(data['currentCell']['mazeGuid'],data['currentCell']);

    else:
      print response.status
      print response.reason
      print response.msg
      print 'No json response found. Something went wrong.'

    conn.close()

  else:
    print 'No url to send a request. Something went wrong.'

  return

def findDirection(currentCell):
  global unexplored
  _direction = ''
  num = 0
  for d in directions:
    if currentCell[d] == 'UNEXPLORED':
      num += 1
      if _direction == '':
        _direction = d

  if num > 1:
    unexplored.append(currentCell)

  print len(unexplored)
  return _direction

index()
