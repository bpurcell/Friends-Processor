<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$config['cache']  = array('container' => 'File',
                           'ttl'       => 3600,
                           'File'      => array('store'           => BASEPATH.'cache/',
                                                'auto_clean'      => 10, 
                                                'auto_clean_life' => 3600,                         
                                                'auto_clean_all'  => false));
                    