<div id="map_canvas" style="width: 600px; height: 600px;"></div>
<script src="../friends_addresses_json/<?=$fb_data['me']['id']?>" type="text/javascript" ></script>
<script>

     var infowindow = new google.maps.InfoWindow();
        $(document).ready(function () {

            initialize();

        });
        function initialize() {
          var center = new google.maps.LatLng(37.4419, -122.1419);

          var map = new google.maps.Map(document.getElementById('map_canvas'), {
            zoom: 3,
            center: center,
            mapTypeId: google.maps.MapTypeId.ROADMAP
          });

          var markers = [];
          for (var i = 0; i < 100; i++) {
              
              data = map_data;
              
            var latLng = new google.maps.LatLng(data[i].lat,
                data[i].lon);
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