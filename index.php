<?php
session_start();
 // include_once('fbconfig.php');
 require_once __DIR__ . '/fbapp/src/Facebook/autoload.php';

 //require 'config.php';
 $fb = new Facebook\Facebook([
  'app_id' => '1575668789121612',
  'app_secret' => 'ce4476d96c08d20e93bbeb3064acfaac',
  'default_graph_version' => 'v2.10',
  ]);

$helper = $fb->getRedirectLoginHelper();

$permissions = ['email','user_photos']; 
	
try {
	if (isset($_SESSION['facebook_access_token'])) {
		$accessToken = $_SESSION['facebook_access_token'];
	} else {
  		$accessToken = $helper->getAccessToken();
	}
} catch(Facebook\Exceptions\FacebookResponseException $e) {

 	echo 'Graph returned an error: ' . $e->getMessage();

  	exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {

	echo 'Facebook SDK returned an error: ' . $e->getMessage();
  	exit;
 }
//  echo  $_SESSION['FBID'];

 ?>
 
 
 <!DOCTYPE html>
<html lang="en">
<head>
	    <title>Facebook Gallery</title>
    <meta charset="utf-8">
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="PHP Facebook Album Gallery">
    <meta name="author" content="John Veldboom">
	
	
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prettyPhoto/3.1.6/css/prettyPhoto.css"/>
	
	
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="aghdsha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prettyPhoto/3.1.6/js/jquery.prettyPhoto.min.js" type="text/javascript" charset="utf-8"></script>
<script src="js/spin.min.js"></script>
<script src="js/responsiveslides.min.js"></script>

    
	<style>
        body { padding-top: 70px; }
        .thumbnail img {
            overflow: hidden;
            height: 250px;
            width: 500%;
        }
						
    </style>
</head>

<body>
 
 
 <?php
 


if (isset($accessToken)) {
	if (isset($_SESSION['facebook_access_token'])) {
		$fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
	} else {

		$_SESSION['facebook_access_token'] = (string) $accessToken;

		$oAuth2Client = $fb->getOAuth2Client();

		$longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);

		$_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;

		$fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
	}

	try {
		$profile_request = $fb->get('/me?fields=name,first_name,last_name,email');
		$profile = $profile_request->getGraphNode()->asArray();
		$requestPicture = $fb->get('/me/picture?redirect=false&height=21'); //getting user picture
		$requestProfile = $fb->get('/me'); // getting basic info
		$picture = $requestPicture->getGraphUser();
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
		echo 'Graph returned an error: ' . $e->getMessage();
		session_destroy();
		header("Location: ./");
		exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {

		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}

	
?>	
	
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <a class="navbar-brand">Facebook Gallery</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse" style="width:25%; float:right">
            <ul class="nav navbar-nav">
                <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo "<img src='".$picture['url']."'/>"." "." ".$profile['name']; ?> <b class="caret"></b></a>
                    <ul class="dropdown-menu">
						<li><a href="?fid=me"> <?php echo $profile['name']." "; ?> Gallery</a></li>
						<li><a href="?fid=221167777906963">PHP + Wordpress</a></li>
						<li><a href="?fid=Lamborghini">Lamborghini</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>

		</div>

		

</nav>
	    <div class="container navbar">
        <a href="#" id="download-all-albums" class="btn btn-info btn-lg">
          <span class="glyphicon glyphicon-download-alt"></span> Download ALL
        </a>
		<a href="#" id="download-selected-albums" class="btn btn-info btn-lg">
          <span class="glyphicon glyphicon-download-alt" ></span> Download Selected
        </a>
		<a href="#" id="move_all" class="btn btn-info btn-lg">
          <span class="glyphicon glyphicon-cloud-download"></span> Move ALL
        </a>
		 <a href="#" id="move-selected-albums" class="btn btn-info btn-lg">
          <span class="glyphicon glyphicon-cloud-download"></span> Move Selected
        </a>
		
 
    </div>

			

     <!-- Album download report window -->   
     <div class="">
         <span id="loader" ></span>
       <div class="modal fade" id="download-modal" tabindex="-1"
					role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">
									<span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
								</button>
								<h4 class="modal-title" id="myModalLabel">Albums Report</h4>
							</div>
							<div class="modal-body" id="display-response">
								<!-- Response is displayed over here -->
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default"
									data-dismiss="modal">Close</button>
							</div>
						</div>
					</div>
   </div>
         </div>
		 

<div class="container-fluid">
    <?php

    if(empty($_GET['fid'])){$_GET['fid'] = 'me';} 

    require('gallery.php');
	?>
	

<?php
    $config = array(
        'page_name' => $_GET['fid'],
        'app_id' => '1575668789121612',
        'app_secret' => 'ce4476d96c08d20e93bbeb3064acfaac',
        'breadcrumbs' => true,
        'cache' => array(
            'location' => 'cache', 
            'time' => 7200
        )
    );

   $gallery = new FBGallery($config);
    echo $gallery->display();

    ?>
</div>


<script type="text/javascript" charset="utf-8">
$(function () {
    $("a[rel^='prettyPhoto']").prettyPhoto({theme: 'dark_rounded',social_tools:'',deeplinking: false});
    $("[rel=tooltip]").tooltip();
});


$( document ).ready(function() {
				var opts = {
				  lines: 13 // The number of lines to draw
, length: 56 // The length of each line
, width: 22 // The line thickness
, radius: 42 // The radius of the inner circle
, scale: 1 // Scales overall size of the spinner
, corners: 1 // Corner roundness (0..1)
, color: '#000' // #rgb or #rrggbb or array of colors
, opacity: 0.25 // Opacity of the lines
, rotate: 0 // The rotation offset
, direction: 1 // 1: clockwise, -1: counterclockwise
, speed: 1 // Rounds per second
, trail: 60 // Afterglow percentage
, fps: 20 // Frames per second when using setTimeout() as a fallback for CSS
, zIndex: 2e9 // The z-index (defaults to 2000000000)
, className: 'spinner' // The CSS class to assign to the spinner
, top: '70%' // Top position relative to parent
, left: '50%' // Left position relative to parent
, shadow: false // Whether to render a shadow
, hwaccel: false // Whether to use hardware acceleration
, position: 'absolute' // Element positioning Element positioning // Left position relative to parent
				};
				var target = document.getElementById('loader');

					
	function append_download_link(url) {
           var spinner = new Spinner(opts).spin(target);
					$.ajax({
						url:url,
						success:function(result){
                                                        
							$("#display-response").html(result);
                                                        spinner.stop();
							$("#download-modal").modal({
								show: true
							});
						}
					});
				}
    
	$("#download-all-albums").on("click", function() {
        append_download_link("download_album.php?zip=1&all_albums=all_albums");

	});
//single download
    				$(".single-download").on("click", function() {
        
					var rel = $(this).attr("rel");
					var album = rel.split(",");

					append_download_link("download_album.php?zip=1&single_album="+album[0]+","+album[1]);
				});

  //giffy and selective function  
     var count=0;
     
   $(function(){
       $("#heading1 h2").html("select your albums  and downlaod and make my giffy smile  ;-)").fadeOut(10000);
     
   });
       
      
    
    $('input[type="checkbox').click(function() {
       
        
        if ($(this).is(':checked') ) {
           count++;
           
           $("p").text(count);
           
         $(".design").html("<img src='images/smile-cart.jpg' height='250px';>");    
     
            
            
        }
       if(!$(this).is(':checked')){
            count--;
            
           $(".design").html("<img src='images/sad-cart.png 'height='250px';>");
          
        }
        function display(){
            $("h2").html("Album Selected :" +count);
            
           
        }
        
        display();
         
    });
    
    
    
    //get selected data/lib
    
    function get_all_selected_albums() {
        
					var selected_albums;
					var i = 0;
					$(".select-album").each(function () {
						if ($(this).is(":checked")) {
							if (!selected_albums) {
								selected_albums = $(this).val();
							} else {
								selected_albums = selected_albums + "/" + $(this).val();
							}
						}
					});
					return selected_albums;
				}
//selected data
    $("#download-selected-albums").on("click", function() {
        
					var selected_albums = get_all_selected_albums();
					append_download_link("download_album.php?zip=1&selected_albums="+selected_albums);
				});
                                
                                function getParameterByName(name) {
					name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
					var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
						results = regex.exec(location.search);
					return results === null ? "null" : decodeURIComponent(results[1].replace(/\+/g, " "));
				}

				function display_message( response ) {
					if ( response == 1 ) {
						$("#display-response").html('<div class="alert alert-success" role="alert">Album(s) is successfully moved to Picasa</div>');
						$("#download-modal").modal({
							show: true
						});
					} else if ( response == 0 ) {
						console.log(response);
						$("#display-response").html('<div class="alert alert-danger" role="alert">Due to some reasons album(s) is not moves to Picasa</div>');
						$("#download-modal").modal({
							show: true
						});
					}
				}

				get_params();

				function get_params() {
					var response = getParameterByName('response');
					display_message(response);
				}
				

				var google_session_token = '<?php echo $google_session_token;?>';

				function move_to_picasa(param1, param2) {
					if (google_session_token) {
						var spinner = new Spinner(opts).spin(target);

						$.ajax({
							url:"download_album.php?ajax=1&"+param1+"="+param2,
							success:function(result){
								spinner.stop();
								display_message(result);
							}
						});
					} else {
						window.location.href = "libs/google_login.php?"+param1+"="+param2;
					}
				}

				$(".move-single-album").on("click", function() {
					var single_album = $(this).attr("rel");
					move_to_picasa("single_album", single_album);
				});

				$("#move-selected-albums").on("click", function() {
					var selected_albums = get_all_selected_albums();
					move_to_picasa("selected_albums", selected_albums);
				});

				$("#move_all").on("click", function() {
					move_to_picasa("all_albums", "all_albums");
				});
                 
	});
	
</script>	
		


<?php } else { 

	$loginUrl = $helper->getLoginUrl('http://www.harshad75.ga/', $permissions);

?>


        <div class="container" style="margin-top:20px;">
	
	<h2 class="text-center" >WELCOME TO FACEBOOK GALLERY</h2>

	
        
        <div id="loginbox" style="margin-top:20px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">                    
            <div class="panel panel-info" >
                    <div class="panel-heading">
                        <div class="panel-title">Sign In</div>
                        <div style="float:right; font-size: 80%; position: relative; top:-10px"><a href="#">Forgot password?</a></div>
                    </div>     

                    <div style="padding-top:30px" class="panel-body" >

                        <div style="display:none" id="login-alert" class="alert alert-danger col-sm-12"></div>
                            
                        <form id="loginform" class="form-horizontal" role="form">
                                    
                            <div style="margin-bottom: 25px" class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                                <input id="login-username" type="text" class="form-control" name="username" value="" placeholder="username or email">                                        
                            </div>
                                
                            <div style="margin-bottom: 25px" class="input-group">
                                <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                                <input id="login-password" type="password" class="form-control" name="password" placeholder="password">
                            </div>
                                
                            <div class="input-group">
                              <div class="checkbox">
                                <label>
                                  <input id="login-remember" type="checkbox" name="remember" value="1"> Remember me
                                </label>
                              </div>
                            </div>

                            <div style="margin-top:10px" class="form-group">
                                <div class="col-sm-12 controls">
                                  <a id="btn-login" href="#" class="btn btn-success">Login  </a>
                                  <a id="btn-fblogin" href="<?php echo $loginUrl; ?>" class="btn btn-primary">Login with Facebook</a>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-12 control">
                                    <div style="border-top: 1px solid#888; padding-top:15px; font-size:85%" >
                                        Don't have an account! 
                                    <a href="#">Sign Up Here</a>
                                    </div>
                                </div>
                            </div>

                            </form>

                        </div>                     
                    </div>  
        </div>
		
   </div>		

   


<?php	} ?>

</body>
</html>