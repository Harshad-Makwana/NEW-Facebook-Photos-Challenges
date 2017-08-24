<?php
require_once('fbconfig.php');


$zip_folder = "";

$album_download_directory = 'download/' . uniqid () . '/';

mkdir ( $album_download_directory, 0777 );

function download_album($album_download_directory, $album_id, $album_name) {
	$album_photos = datafromfacebook ( '/' . $album_id . '/photos?fields=source' );
	$album_directory = $album_download_directory . $album_name;
	if (! file_exists ( $album_directory )) {
		mkdir ( $album_directory, 0777 );
	}
	$i = 1;
	
	foreach ( $album_photos ['data'] as $album_photo ) {
		$album_photo = ( array ) $album_photo;
		file_put_contents ( $album_directory . '/' . $i . ".jpg", fopen ( $album_photo ['source'], 'r' ) );
		$i ++;
	}
}

// ---------- For 1 album download -------------------------------------------------//
if (isset ( $_GET ['single_album'] ) && ! empty ( $_GET ['single_album'] )) {
	$single_album = explode ( ",", $_GET ['single_album'] );
	
	download_album ( $album_download_directory, $single_album [0], $single_album [1] );
}

// ---------- For Selected Albums download -----------------------------------------//
if (isset ( $_GET ['selected_albums'] ) and count ( $_GET ['selected_albums'] ) > 0) {
	$selected_albums = explode ( "/", $_GET ['selected_albums'] );
	
	foreach ( $selected_albums as $selected_album ) {
		$selected_album = explode ( ",", $selected_album );
		download_album ( $album_download_directory, $selected_album [0], $selected_album [1] );
	}
}

// ---------- Download all album code -------------------------------------------------//
if (isset ( $_GET ['all_albums'] ) && ! empty ( $_GET ['all_albums'] )) {
	if ($_GET ['all_albums'] == 'all_albums') {
		
		// get data
		$albums = $album_photos = datafromfacebook ( '/me/albums?fields=id,name' );
		
		if (! empty ( $albums )) {
			foreach ( $albums ['data'] as $album ) {
				$album = ( array ) $album;
				download_album ( $album_download_directory, $album ['id'], $album ['name'] );
			}
		}
	}
}

if (isset ( $_GET ['zip'] )) {
	require_once ('zipper.php');
	$zipper = new zipper ();
	echo $zipper->get_zip ( $album_download_directory );
} else {
	
	$redirect = 'location:download/move_to_picasa.php?album_download_directory=' . $album_download_directory;
	if (isset ( $_GET ['ajax'] )) {
		$redirect = $redirect . '&ajax=1';
	}
	header ( $redirect );
}
?>
