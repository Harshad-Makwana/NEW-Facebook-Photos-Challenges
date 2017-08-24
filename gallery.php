<?php

require_once __DIR__ . '/fbapp/src/Facebook/autoload.php';

class FBGallery
{

    public function __construct($config) {
        $this->fb = new \Facebook\Facebook([
            'app_id' => $config['app_id'],
            'app_secret' => $config['app_secret'],
            'default_graph_version' => 'v2.8'
        ]);
		
		$this->helper = $this->fb->getCanvasHelper();

		$this->permissions = ['user_photos','email'];


 try {
	if (isset($_SESSION['facebook_access_token'])) {
	$this->access_token = $_SESSION['facebook_access_token'];
	} else {
  		$this->access_token = $this->helper->getAccessToken();
	}
} catch(Facebook\Exceptions\FacebookResponseException $e) {
 	echo 'Graph returned an error: ' . $e->getMessage();
  	exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
	echo 'Facebook SDK returned an error: ' . $e->getMessage();
  	exit;
 }
	if (isset($this->access_token)) {

	if (isset($_SESSION['facebook_access_token'])) {

	$this->fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
	} else {

		$_SESSION['facebook_access_token'] = (string) $this->access_token;


		$this->oAuth2Client = $this->fb->getOAuth2Client();


		$this->longLivedAccessToken = $this->oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);

		$_SESSION['facebook_access_token'] = (string) $this->longLivedAccessToken;

		$this->fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
	}

	try {
		$this->request = $this->fb->get('/me');

	} catch(Facebook\Exceptions\FacebookResponseException $e) {
		if ($e->getCode() == 190) {
			unset($_SESSION['facebook_access_token']);
			$this->helper = $this->fb->getRedirectLoginHelper();
			$this->loginUrl = $this->helper->getLoginUrl('https://apps.facebook.com/gameappk', $this->permissions);
			echo "<script>window.top.location.href='".$this->loginUrl."'</script>";
			exit;
		}
	} catch(Facebook\Exceptions\FacebookSDKException $e) {

		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}

        $this->page_name = $config['page_name'];
        $this->breadcrumbs = $config['breadcrumbs'];
        $this->cache = $config['cache'];
    }else {
	$this->helper = $this->fb->getRedirectLoginHelper();
	$this->loginUrl = $this->helper->getLoginUrl('https://www.harshad75.ga/', $this->permissions);
	echo "<script>window.top.location.href='".$this->loginUrl."'</script>";
	}
	}
    public function display(){
        try{
            if(empty($_GET['id'])){
                return $this->displayAlbums();
            }

            return $this->displayPhotos($_GET['id'],$_GET['title']);

        } catch(Exception $e){
            return 'Unable to display gallery due to the following error: '.$e->getMessage();
        }
    }
    private function getData($album_id='',$type=''){
        if($type == 'photos'){
            $url = 'https://graph.facebook.com/'.$album_id.'/photos?access_token='.$this->access_token.'&limit=100&fields=images,id,description,link,picture,source,name,cover_photo,count';

        } else {
            $url = 'https://graph.facebook.com/'.$this->page_name.'/albums?access_token='.$this->access_token.'&limit=100&fields=images,id,description,link,picture,source,name,cover_photo,count';
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER,0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        $return_data = curl_exec($ch);

        $json_array = json_decode($return_data,true);
        if(isset($json_array['error'])){
            throw new Exception($json_array['error']['message']);
        }

        return $json_array;
    }
    private function displayAlbums(){
        $cache = $this->getCache($this->page_name); 
        if($cache) return $cache;

        $gallery = '';
        $albums = $this->getData($this->page_name,$type='albums');

        foreach($albums['data'] as $album){
            if($album['count'] > 0) {
				
				    $cover_photo_id = isset($album['cover_photo']['id'])?$album['cover_photo']['id']:'';
					$alb_nm = mb_substr($album['name'], 0, 15);
	
				//					/	//	position: relative; left: 10%;	
                $gallery .= '
<style>
				
.redtext {
        color: white;
		font-size: 100%;
}
.absolute {
    position: absolute;
    top: 5px;
    width: 260px;
    height: 30px;
}
</style>
				<div class="fb-album col-lg-3 col-sm-4 col-xs-6">	
								<a href="?id='.$album['id'].'&title='.urlencode($album['name']).'" class="thumbnail" rel="tooltip" data-placement="bottom" title="'.$album['name'].' ('.$album['count'].')">
								<img src="https://graph.facebook.com/v2.9/'.$cover_photo_id.'/picture?access_token='.$this->access_token.'">
								 </a>
								 <div class="absolute">
								 <a>
								<input type="checkbox" style="width:10%; float:left" class="select-album" title="select album" value="'.$album['id'].','.$album['name'].'" />
								</a>
								<a>
								<fonat class="redtext"><b>'.$alb_nm.'('.$album['count'].')</b></fonat>
								</a>
								<a>
								<button rel="'.$album['id'].','.$album['name'].'" class="move-single-album" style="width:15%; float:right" title="Download Album">
								<span class="glyphicon glyphicon-cloud-download" aria-hidden="true"></span>
								</button>
								</a>
								<a>
								<button rel="'.$album['id'].','.$album['name'].'" class="single-download" style="width:15%; float:right" title="Download Album">
								<span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span>
								</button>
								</a>


								</div>
                            </div>';	
            }
        }

//<h4><?php echo $alb_nm.' ('.count($album_photos['data']).')';
// data-src='.album['source'].'
        $gallery = '<ul class="thumbnails">'.$gallery.'</ul>';

        if($this->breadcrumbs){
            $crumbs = array('Gallery' => $_SERVER['PHP_SELF']);
            $gallery = $this->addBreadCrumbs($crumbs).$gallery;
        }

        $this->saveCache($this->page_name,$gallery);

        return $gallery;
    }

    private function displayPhotos($album_id,$title='Photos'){
        $cache = $this->getCache($album_id); 
        if($cache) return $cache;

        $photos = $this->getData($album_id,$type='photos');
        if(count($photos) == 0) return 'No photos in this gallery';

        $gallery = '';
        foreach($photos['data'] as $photo)
        {
			
	  $cover_photo_id1 = isset($photo['id'])?$photo['id']:'';
								
            $gallery .= '<div class="col-lg-3 col-sm-3 col-xs-6">
                            <a href="'.$photo['source'].'" rel="prettyPhoto['.$album_id.']" title="" class="thumbnail">
								<img src="https://graph.facebook.com/'.$cover_photo_id1.'/picture?access_token='.$this->access_token.'">
                            </a>
                        </div>';					
        }

        if($this->breadcrumbs){
            $crumbs = array('Gallery' => $_SERVER['PHP_SELF'],  $title => '');
            $gallery = $this->addBreadCrumbs($crumbs).$gallery;
        }

        $this->saveCache($album_id,$gallery); 

        return $gallery;
    }


    private function addBreadCrumbs($crumbs_array){
        $crumbs = '';
        if(is_array($crumbs_array)){
            foreach($crumbs_array as $title => $url){
                $crumbs .= '<li><a href="'.$url.'">'.stripslashes($title).'</a></li>';
            }

            return '<ol class="breadcrumb">'.$crumbs.'</ol>';
        }
    }
    private function saveCache($id,$html){
        if($this->cache && is_writable($this->cache['location']))
        {
            $fp = @fopen($this->cache['location'].'/'.$id.'.html', 'w');
            if (false == $fp) {
                $error = error_get_last();
                throw new Exception('Unable to save cache due to '.$error['message']);
            } else {
                fwrite($fp, $html);
                fclose($fp);
            }} }
    private function getCache($id){
        if($this->cache) {
            $cache_file = $this->cache['location'].'/'.$id.'.html';
            if(file_exists($cache_file) AND filemtime($cache_file) > (date("U") - $this->cache['time'])) {
                return file_get_contents($cache_file);
            }
        }
        return false;
    }
}