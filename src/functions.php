<?php

function checkCookies($name, &$message)
{
	if (!empty($_COOKIE[$name])) {
		$message[$name] = $_COOKIE[$name];
	} else {
		$message[$name] = '';
	}
	if (!empty($_COOKIE[$name . '-error'])) {
		$message[$name . '-error'] = "<div class='form__container form__container_err'><span class='form__span'>{$_COOKIE[$name . '-error']}</span></div>";
		setcookie($name . '-error', '', time() - 60 * 60 * 24);
	} else {
		$message[$name . '-error'] = '';
	}
}
function writeCookies($name, &$errors)
{
	if (isset($errors[$name])) {
		setcookie($name . '-error', $errors[$name], time() + 60 * 60 * 24);
	} else {
		setcookie($name, $_POST[$name], time() + 60 * 60 * 24 * 365);
	}
}
function gen_password($length = 12)
{
	$chars = 'qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP';
	$size = strlen($chars) - 1;
	$password = '';

	while ($length--) {
		$password .= $chars[random_int(0, $size)];
	}

	return $password;
}
