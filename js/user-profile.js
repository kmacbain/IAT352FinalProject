
$('document').ready(function(){
  const urlParams = new URLSearchParams(window.location.search);
  const uid = urlParams.get('userid');

  // console.log(ifLogin);

  var request=$.ajax({
    type:'POST',
    url:'server/checkLogin.php?query=checkLogin',
    data:{},
    dataType:'text'
  });
  request.done(function(data){
    var login_userid=data;
    if(login_userid==uid){
      console.log('log in as the user');
      $('#button-index-Following').addClass('hidden');
    }
  });
  request.fail(function(msg){
    console.log("error", msg);
  });   

  var type='posts';
  pullUserProfile(uid,type);
  $('.select-posts').click(function(){
    type="posts";
    $('.select-posts').addClass('style-selected-underline');
    $('.select-collect').removeClass('style-selected-underline');
    console.log(type);
    pullUserProfile(uid,type);
  });

  $('.select-collect').click(function(){
    type="collection";
    $('.select-posts').removeClass('style-selected-underline');
    $('.select-collect').addClass('style-selected-underline');    
    console.log(type);
    pullUserProfile(uid,type);
  });

  $('#button-index-FilterBy').click(function(){
    var keyword=$('#input-filter').val();
    $.ajax({
      type:'POST',
      url:'server/base.php?query=loadUser&uid='+uid+'&type='+type+'&tag='+keyword,
      data:{},
      dataType:'text',
      success:function(data){
        var result=JSON.parse(data);    
        $('.section-userWorkDisplay').html(result.userpost);
      },
      error:function(data){
        console.log("an error happened, transaction failed");
      }
    }); 
  });
  $('#button-index-All').click(function(){
    pullUserProfile(uid,type);    
    $('#button-index-All').addClass('selected');
    $('#button-index-Images').removeClass('selected');
    $('#button-index-Articles').removeClass('selected');
  });
  $('#button-index-Images').click(function(){
    $('#button-index-Images').addClass('selected');
    $('#button-index-All').removeClass('selected');
    $('#button-index-Articles').removeClass('selected');

    $.ajax({
      type:'POST',
      url:'server/base.php?query=loadUser&uid='+uid+'&type='+type+'&filter=images',
      data:{},
      dataType:'text',
      success:function(data){
        var result=JSON.parse(data);    
        $('.section-userWorkDisplay').html(result.userpost);
      },
      error:function(data){
        console.log("an error happened, transaction failed");
      }
    });     
  });
  $('#button-index-Articles').click(function(){
    $('#button-index-Articles').addClass('selected');
    $('#button-index-All').removeClass('selected');
    $('#button-index-Images').removeClass('selected');

    $.ajax({
      type:'POST',
      url:'server/base.php?query=loadUser&uid='+uid+'&type='+type+'&filter=articles',
      data:{},
      dataType:'text',
      success:function(data){
        var result=JSON.parse(data);    
        $('.section-userWorkDisplay').html(result.userpost);
      },
      error:function(data){
        console.log("an error happened, transaction failed");
      }
    });     
  });

});