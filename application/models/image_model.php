<?php

/*
 * Copyright 2011 Facebook, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */


class Image_model extends CI_Model {

    public function __construct(){
        parent::__construct();
    }

    function image_creator($uid)
    {
        
        $info = $this->facebook_model->friend_info($uid);
        var_dump($info);
        // Create a test source image for this example
        $im = imagecreatetruecolor(800, 600);
        
        $text_color = imagecolorallocate($im, 233, 14, 91);
        imagestring($im, 1, 5, 5,  $uid, $text_color);
        
        // start buffering
        ob_start();
        // output jpeg (or any other chosen) format & quality
        imagejpeg($im, NULL, 85);
        // capture output to string
        $contents = ob_get_contents();
        // end capture
        ob_end_clean();
        
        // be tidy; free up memory
        imagedestroy($im);
        
        // lastly (for the example) we are writing the string to a file
        $fh = fopen("./written_images/".$uid.".jpg", "w" );
            fwrite( $fh, $contents );
        fclose( $fh );
    
    }
}
?>