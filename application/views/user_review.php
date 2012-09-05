
    <div id="friend_div">
        <h1><?=$profile['names'][0]?> <?=$profile['names'][1]?> has <span id="total_friends"><?=$info['total_friends'] ?></span> friends</h1>
        <p id="more_popular"><?=$profile['pronoun']?> has <span id="popular_number"><?=$info['friends_comparison'] ?></span> friends who have more friends.</p>
    </div>
    <hr>
    <div id="relationship_div">
        <h2><?=$profile['names'][0]?>s friends are in these types of relationships.</h2>
        <div id="<?=$profile['uid']?>_relationships" style="width:100%;height:600px;"></div>
    </div>
    <hr>
    <div id="age_div">    
        <h2><?=$profile['names'][0]?>s friends are on average <span id="average_age"><?=$info['average_age']?></span> years old.</h2>
        <div id="<?=$profile['uid']?>_ages" style="width:100%;height:400px;"></div>
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
        <h2><?=$profile['pronoun']?> shares the most friends with these people</h2>
        <?php
        foreach($info['most_mutual'] as $friend):?>
            <div class="mutual_friend">
                <div class="mutual_friend_image"><img src="https://graph.facebook.com/<?=$friend['uid']; ?>/picture" alt="" class="pic" /></div>
                <h3><?=$friend['name']?></h3>
                <p><?=$profile['pronoun']?> has <?=$friend['mutual_friend_count']?> friends in common</p>
            </div>
        <?php endforeach;
   	    ?>
   	    
    </div>
    <hr>
    <div id="eduction_div">
        <h2><?=$profile['names'][0]?>s friends are smart</h2>
        <p id="grad">There are <span class="number_callout"><?=$info['education_breakdown']['grad'][0]['count']?></span> friends who have or are getting their post grad degree</p>
        <p id="college">and <span class="number_callout"><?=$info['education_breakdown']['college'][0]['count']?></span> friends are in or did graduate college.</p>
    </div>
    
    <hr>
    <div id="college_div">
        <h2><?=$profile['names'][0]?>s friends went to <span class="number_callout"><?=$info['college_count']['count']?></span> different colleges</h2>
        <div id="colleges" style="width:100%;height:400px;"></div>
    </div>
            
    <script type="text/javascript">
     $(document).ready(function(){
         
         var data = [
        <?php 
            $count = count($info['friends_breakdown']);
            foreach($info['friends_breakdown'] as $key=>$relationship):
                if($count == ($key+1)):
                     echo '{ label: "'.$relationship['relationship_status'].'",  data: '.$relationship['count'].'}';
                else:
                    echo '{ label: "'.$relationship['relationship_status'].'",  data: '.$relationship['count'].'},
                    ';
                endif;
 	        endforeach;
 	    
 	    ?>
 	    ];

     	// GRAPH 6
     	$.plot($("#<?=$profile['uid']?>_relationships"), data, 
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

        // example 2 - basic bar graph
        $.plot(
           $("#<?=$profile['uid']?>_ages"),
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
            // example 2 - basic bar graph
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

     </script>
    