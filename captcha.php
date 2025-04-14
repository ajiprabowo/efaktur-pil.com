<?php
	session_start();
	$cap = substr(str_shuffle("01234567890abcdefghijklmnopqrstuvwxyz"), 0, 6); // string akan diacak sebanyak 6 karakter 0-9 dan a-z
	$_SESSION['tiket_cap'] = $cap;

	$gambar = imagecreate(60, 20); // ukuran width=60, height=20
	$wrn = imagecolorallocate($gambar, 0, 0, 0); // warna kotak
	$wrt = imagecolorallocate($gambar, 255, 255, 255); // warna tulisan
	imagefilledrectangle($gambar, 0, 0, 50, 20, $wrn);
	imagestring($gambar, 10, 3, 3, $cap, $wrt);
	imagejpeg($gambar);
?>