<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '192.168.1.7'):
    $config['appId']  = '206752992690510';
    $config['secret'] = 'e7eba60acf8de28666b0e6465bd50ffa';
else:
    $config['appId']  = '245982148820971';
    $config['secret'] = 'd6451de192f254644d9a3a229d0fbcec';
endif;
