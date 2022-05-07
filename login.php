<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	if (!empty($_COOKIE[session_name()]) && !empty($_SESSION['login'])) {
		session_destroy();
		header("Location: index.php");
		exit();
	}

	if (!empty($_COOKIE['login-request-error'])) {
		setcookie("login-request-error", '', time() - 60 * 60 * 24);
		$loginHeader =
			"<div class='form__header'>
				<div class='form__contaner form__container_err'>
					<span class='form__span'>Что-то пошло не так! =(</span>
				</div>
			</div>";
	} elseif (!empty($_COOKIE['login-auth-error'])) {
		setcookie('login-auth-error', '', time() - 60 * 60 * 24);
		$loginHeader =
			"<div class='form__header'>
				<div class='form__contaner form__container_err'>
					<span class='form__span'>Неверный логин и/или пароль!</span>
				</div>
			</div>";
	} else {
		$loginHeader =
			"<div class='form__header'>
				<div class='form__contaner'>
					<span class='form__span form__span_header'>Авторизируйтесь</span>
				</div>
			</div>";
	}

	$message = array('login-error' => '', 'password-error' => '');
	if (!empty($_COOKIE['login-error'])) {
		$message['login-error'] =
			"<div class='form__container form__container_err'>
				<span class='form__span'>{$_COOKIE['login-error']}</span>
			</div>";
		setcookie('login-error', '', time() - 60 * 60 * 24);
	}

	if (!empty($_COOKIE['password-error'])) {
		$message['password-error'] =
			"<div class='form__container form__container_err'>
				<span class='form__span'>{$_COOKIE['password-error']}</span>
			</div>";
		setcookie('password-error', '', time() - 60 * 60 * 24);
	}

	require_once("formlogin.php");
	exit();
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {

	$requestError = false;
	if (!empty($_POST)) {
		if (empty($_POST["login"])) {
			$errors['login'] = "Введите логин";
		}

		if (empty($_POST["password"])) {
			$errors['password'] = "Введите пароль";
		}
	} else {
		$requestError = true;
	}

	if ($requestError) {
		setcookie("login-request-error", '1', time() + 60 * 60 * 24);
		header("Location: login.php");
	} else {
		if (isset($errors['login'])) {
			setcookie('login-error', $errors['login'], time() + 60 * 60 * 24);
		}
		if (isset($errors['password'])) {
			setcookie('password-error', $errors['password'], time() + 60 * 60 * 24);
		}
	}

	if (isset($errors)) {
		header("Location: login.php");
		exit();
	}

	$userLogin = $_POST["login"];
	$userPassword = $_POST["password"];

	require_once("src/db.php");
	$db = new PDO("mysql:host=$dbServerName;dbname=$dbName", $dbUser, $dbPassword, array(PDO::ATTR_PERSISTENT => true));

	$success = false;
	try {
		$sql =
			"SELECT * FROM user_authentication
			WHERE login = :login";
		$stmt = $db->prepare($sql);
		$stmt->execute(array('login' => $userLogin));
		$result = $stmt->fetch();

		if (!empty($result)) {
			$success = password_verify($userPassword, $result['password']);
			$userId = $result['id'];
		}
	} catch (PDOException $e) {
		print('Error : ' . $e->getMessage());
		exit();
	}

	if ($success) {
		$_SESSION['login'] = $userLogin;
		$_SESSION['loginid'] = $userId;
	} else {
		setcookie('login-auth-error', '1', time() + 60 * 60 * 24);
		header("Location: login.php");
		exit();
	}
}
header("Location: index.php");
exit();
