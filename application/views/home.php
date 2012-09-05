

    <div id="friend_update_count">You have <?=$friend_count?> friends to get data for. This should take about <?=round($friend_count*(15/750))?> seconds</div>
    <div id="loader"><img src="../images/loader.gif"></div>
    <a href="<?=base_url()?>review_friends/<?=$fb_data['uid']?>" id="review_friends_link" style="display:none">Review Friends</a>

<script type="text/javascript">
$(document).ready(function() {
    GetFriends();

});

function GetFriends() {
    var full_url = '<?=base_url()?>start_friend_processing';
    $.ajax({
        url: full_url,
        success: function(data) {

                $('#friend_update_count').hide();
                $('#loader').hide();
                $('#review_friends_link').show();
            }
    });
    // end JSON function
}
</script>