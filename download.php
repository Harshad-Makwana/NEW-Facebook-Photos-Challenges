<?php
		$filename=$_GET['filename'];
		header('Content-disposition: attachment; filename='.$filename);
		header('Content-type: application/zip');
		readfile($filename);
?>