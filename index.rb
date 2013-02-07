#!/usr/bin/env ruby

# Maze Solving script in Ruby
# Copyright Quinn Sakunaga
#
# Licensed under The MIT License
# Redistributions of files must retain the above copyright notice.
#
# @copyright     Copyright Quinn Sakunaga
# @license       MIT License (http://www.opensource.org/licenses/mit-license.php)

require 'rubygems'
require 'uri'
require 'net/http'
require 'json'

$server = '';# Enter Server URI

$unexplored = []
$directions = ['north','east','south','west']

def main()
  url = URI($server+'/api/init')
  post = Net::HTTP::Post.new(url.path)
  post.content_type = 'multipart/form-data'
  response = Net::HTTP.start(url.host, url.port) do |http|
    http.request(post)
  end
  case response
  when Net::HTTPSuccess, Net::HTTPRedirection
    puts response.message
    data = JSON response.body
    if response.code == '200'
      puts data['currentCell']['note']
      explore(data['currentCell']['mazeGuid'],data['currentCell'])
    end
  end
end

def explore(mazeGuid,currentCell)
  url = ''
  direction = ''
  direction = findDirection(currentCell)
  puts direction
  if direction != ''
    url = URI.parse($server + '/api/move')
    params = {'mazeGuid' => mazeGuid, 'direction' => direction.upcase}
  else
    if $unexplored.count > 0
      _last = $unexplored.pop
      url = URI.parse($server + '/api/jump')
      params = {'mazeGuid' => mazeGuid, 'x' => _last['x'], 'y' => _last['y']}
    end
  end
  if url != ''
    post = Net::HTTP::Post.new(url.path)
    post.content_type = 'multipart/form-data'
    post.set_form_data(params)
    response = Net::HTTP.start(url.host, url.port) do |http|
      http.request(post)
    end
    case response
    when Net::HTTPSuccess, Net::HTTPRedirection
      data = JSON response.body
      if response.code == '200'
        if data['currentCell']['atEnd'] == true
          puts data['currentCell']['atEnd']
          puts data['currentCell']['note']
          puts data['currentCell']['x']
          puts data['currentCell']['y']
          puts 'Congratulation!'
        else
          puts '---'
          puts data['currentCell']['x']
          puts data['currentCell']['y']
          puts '---'
          explore(data['currentCell']['mazeGuid'],data['currentCell']);
        end
      end
    end
  end
end

def findDirection(currentCell)
  _direction = ''
  num = 0
  for d in $directions
    if currentCell[d] == 'UNEXPLORED'
      num += 1
      if _direction == ''
        _direction = d
      end
    end
  end
  if num > 1
    $unexplored.push(currentCell)
  end
  puts $unexplored.count
  return _direction
end

main()
