<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    
    <title><?=$title?></title>
    <meta name="description" content="Simple, powerful location-aware apps that can be shared with customers and colleagues"/> 
    <meta name="keywords" content="location, pointrecorder, porosventures, bennington purcell, awareness, app, free, iphone"/> 
    <meta name="author" content="Bennington Purcell - Poros Venturess"/>
    
    <!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <link href="<?=base_url()?>css/reset.css" rel="stylesheet" type="text/css" /><!-- Main Style-->
    <link href="<?=base_url()?>css/facebook.css" rel="stylesheet" type="text/css" /><!-- Main Style-->

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.js"></script>
    <script src="<?=base_url()?>javascript/flot/jquery.flot.js"></script>
    <script language="javascript" type="text/javascript" src="<?=base_url()?>javascript/flot/jquery.flot.pie.js"></script>
    
    
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>
    <script src="<?=base_url()?>javascript/markerclusterer_compiled.js"></script>

      
</head>
<body>
<div id="body_wrap">   
    <div id="control_area">
        <div id="logo">
            <span id="logo_friend">Friend</span>
            <span id="logo_data">Data</span>
        </div>
        <div id="user_info_area">
        <?php if($fb_data['uid']):?>
            <div id="profile_image"></div>
            Welcome <span id="logged_in_user_name"></span>! <br>
            <a href="" id="my_data_link" style="display:none">View your data</a>
        <?php else:?>
            <a href="<?php echo $fb_data['loginUrl']; ?>"><img src="http://static.ak.fbcdn.net/images/fbconnect/login-buttons/connect_light_medium_long.gif"></a><br>
            To get your own Friend Stat Sheet.
        <?php endif;?>
        </div>
    </div>
    <hr>
    <div id="page_content">
    <?php $this->load->view($page); ?>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    GetLoggedInUser() 
});
function GetLoggedInUser() {
    var full_url = '<?=base_url()?>get_logged_in_user';
    $.ajax({
        url: full_url,
        dataType: 'json',
        success: function(data) {
            $('#logged_in_user_name').html(data.me.name);
            $('#profile_image').html('<img src="https://graph.facebook.com/'+data.me.id+'/picture" alt="" class="pic" />');
            $('#my_data_link').attr('href', '../review_friends/'+data.me.id);
            $('#my_data_link').show();
        }
    });
    // end JSON function
}
</script>
</body>
</html>