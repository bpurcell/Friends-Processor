
    <div id="most_this_info">
        <a href="<?=base_url()?>post_stats" class="button_links">Post this data too your Timeline and let people see how cool your friends are.</a>
    </div>
    <hr>
    <div id="friend_div">
        <h1>You have <span id="total_friends"><?=$info['total_friends'] ?></span> friends.</h1>
        <p id="more_popular">You have <span id="popular_number"><?=$info['friends_comparison'] ?></span> friends who have more friends than that.</p>
    </div>
    <hr>
    <div id="relationship_div">
        <h2>Your friends are in these types of relationships.</h2>
        <div id="relationship_graph_wrap">
            <div id="relationship" style="width:70%;height:600px;"></div>
            <div id="relationship_null" style="width:28%;height:300px;"></div>
        </div>
    </div>
    <hr>
    <div id="age_div">    
        <h2>Your friends are on average <span id="average_age"><?=$info['average_age']?></span> years old.</h2>
        <div id="ages" style="width:100%;height:400px;"></div>
    </div>
    <hr>
    <div id="sex_div">
        <h2>They breakdown like this...</h2>
        <?php
        foreach($info['sex_breakdown'] as $sex):
            echo "<div id='wrap_".$sex['sex']."'>";
            $count = $sex['count'];
            while($count > 0){
                echo "<div class='mf_icon ".$sex['sex']."'></div>";
                $count = $count - 1;
            }
            echo "</div>";
        endforeach;
   	    ?>
    </div>
    <hr>
    <div id="most_mutal">
        <h2>You share the most friends with these people</h2>
        <?php
        foreach($info['most_mutual'] as $friend):?>
            <div class="mutual_friend">
                <div class="mutual_friend_image"><img src="https://graph.facebook.com/<?=$friend['uid']; ?>/picture" alt="" class="pic" /></div>
                <h3><?=$friend['name']?></h3>
                <p>You have <?=$friend['mutual_friend_count']?> friends in common</p>
            </div>
        <?php endforeach;
   	    ?>
   	    
    </div>
    <hr>
    <div id="eduction_div">
        <h2>Your friends are smart</h2>
        <p id="grad">You have <span class="number_callout"><?=$info['education_breakdown']['grad'][0]['count']?></span> friends who have or are getting their post grad degree and <span class="number_callout"><?=$info['education_breakdown']['college'][0]['count']?></span> friends are in or did graduate college.</p>
    </div>
    <hr>
    <div id="college_div">
        <h2>Your friends went to <span class="number_callout"><?=$info['college_count']['count']?></span> different colleges</h2>
        <div id="colleges" style="width:100%;height:400px;"></div>
    </div>
    <hr>
    <div id="map">
        <h2>This is where your friends generally located</h2>
        <div id="map_canvas" style="width:100%;height:400px;"></div>
    </div>
     <hr>      
    <div id="most_this_info">
        <a href="<?=base_url()?>post_stats" class="button_links">Post this data too your Timeline and let people see how cool your friends are.</a>
    </div>
    
    <script src="../friends_addresses_json/<?=$fb_data['me']['id']?>" type="text/javascript" ></script>
    <script>

 $(document).ready(function(){
     initialize();
     
     var relationships = [
    <?php 
        $count = count($info['friends_breakdown']);
        $total_in_relationship = 0;
        foreach($info['friends_breakdown'] as $key=>$relationship):
            $total_in_relationship = $total_in_relationship+$relationship['count'];
            if($count == ($key+1)):
                 echo '{ label: "'.$relationship['relationship_status'].'",  data: '.$relationship['count'].'}';
            else:
                echo '{ label: "'.$relationship['relationship_status'].'",  data: '.$relationship['count'].'},
                ';
            endif;
        endforeach;
    
    ?>
    ];
    
     var relationships_null = <?='[{ label: "Have Relationship Status",  data: '.$total_in_relationship.'},{ label: "Don\'t say",  data: '.$info['total_friends'].'}];';?>

 	// Relationship chart
 	$.plot($("#relationship"), relationships, 
    {
            series: {
                pie: { 
                    show: true,
                    radius: 1,
                    label: {
                        show: true,
                        radius: 1,
                        formatter: function(label, series){
                            return '<div style="font-size:15pt;font-weight:bold;text-align:center;padding:2px;color:white;">'+label+'<br/>'+Math.round(series.percent)+'%</div>';
                        },
                       background: { opacity: 0.8 },
                       threshold: 0.1
                    }
                }
            }
    });
    
    // Relationship Chart
 	$.plot($("#relationship_null"), relationships_null, 
    {
            series: {
                pie: { 
                    show: true,
                    radius: 1
                }
            }
    });
    
    var d2 = [
    <?php 
       $count = count($info['age_breakdown']);
       foreach($info['age_breakdown'] as $key=>$age):
           if($count == ($key+1)):
                echo '{ label: "'.$age['decade'].'",  data: '.$age['count'].'}';
           else:
               echo '{ label: "'.$age['decade'].'",  data: '.$age['count'].'},
               ';
           endif;
        endforeach;
    ?>
    ];

    // Bar chart
    $.plot(
       $("#ages"),
       [
        {
          data: [ 
          <?php 
             $count = count($info['age_breakdown']);
             foreach($info['age_breakdown'] as $key=>$age):
                 if($count == ($key+1)):
                    echo '['.$age['decade'].', '.$age['count'].']';
                 else:
                    echo '['.$age['decade'].', '.$age['count'].'],';
                 endif;
  	        endforeach;
   	    ?>
          ],
          bars: {
            show: true,
            barWidth: 5,
            align: "center"
          }   
        }
     ],
     {
       xaxis: {
         ticks: [
           <?php 
              $count = count($info['age_breakdown']);
              foreach($info['age_breakdown'] as $key=>$age):
                  if($count == ($key+1)):
                     echo '['.$age['decade'].', "'.$age['decade'].' somethings"]';
                  else:
                     echo '['.$age['decade'].', "'.$age['decade'].' somethings"],';
                  endif;
   	        endforeach;
    	    ?>
         ]
       }   
     }
    );
    
    // Bar Chart
    $.plot(
       $("#colleges"),
       [
        {
          data: [ 
          <?php 
             $count = count($info['college_popularity']);
             foreach($info['college_popularity'] as $key=>$college):
                 if($count == ($key+1)):
                    echo '['.$key.', '.$college['count'].']';
                 else:
                    echo '['.$key.', '.$college['count'].'],';
                 endif;
  	        endforeach;
   	    ?>
          ],
          bars: {
            show: true,
            barWidth: 1,
            align: "center"
          }   
        }
     ],
     {
       xaxis: {
         ticks: [
           <?php 
              $count = count($info['college_popularity']);
              foreach($info['college_popularity'] as $key=>$college):
                  if($count == ($key+1)):
                     echo '['.$key.', "'.$college['name'].'"]';
                  else:
                     echo '['.$key.', "'.$college['name'].'"],';
                  endif;
   	        endforeach;
    	    ?>
         ]
       }   
     }
    );
});

var infowindow = new google.maps.InfoWindow();
function initialize() {
  var center = new google.maps.LatLng(37.4419, -122.1419);

  var map = new google.maps.Map(document.getElementById('map_canvas'), {
    zoom: 3,
    center: center,
    mapTypeId: google.maps.MapTypeId.ROADMAP
  });

  var markers = [];
  for (var i = 0; i < <?=$info['friends_address_count']?>; i++) {
    data = map_data;
    var latLng = new google.maps.LatLng(data[i].lat,data[i].lon);
    var marker = new google.maps.Marker({
      position: latLng,
      map: map,
      index : i,
      html: "<h3>"+data[i].name+"</h3>"
  });

    google.maps.event.addListener(marker, 'click', function () {
        infowindow.setContent(this.html);
        infowindow.open(map, this);
    });

    markers.push(marker);
  }
  var markerCluster = new MarkerClusterer(map, markers);
}
</script>