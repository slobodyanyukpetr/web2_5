<?php
session_start();
require_once("src/functions.php");

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	if (!empty($_COOKIE['save'])) {
		setcookie("save", '', time() - 60 * 60 * 24);
		setcookie("login", '', time() - 60 * 60 * 24);
		setcookie("password", '', time() - 60 * 60 * 24);

		$fheader = "<div class='form__container form__container_good'>
		<span class='form__span'>Ваши данные отправленны!</span>
		</div>
		<div class='form__container'>
			<p class='form__p'>Вы можете <a href='login.php' class='form__a'>войти</a>!</p>
			<p class='form__p'>По логину: <strong>{$_COOKIE['login']}</strong></p>
			<p class='form__p'>И паролю: <strong>{$_COOKIE['password']}</strong></p>
		</div>";
	} elseif (!empty($_COOKIE['request-error'])) {
		setcookie("request-error", '', time() - 60 * 60 * 24);
		$fheader = "<div class='form__container form__container_err'><span class='form__span'>Что-то пошло не так! =(</span></div>";
	} elseif (!empty($_COOKIE['update'])) {
		setcookie("update", '0', time() - 60 * 60 * 24);
		$fheader =
			"<div class='form__container form__container_good'>
				<span class='form__span'>Ваши данные обновленны!</span>
			</div>";
	} else {
		$fheader = "<div class='form__contaner'><span class='form__span form__span_header'>ЗАПОЛНИТЕ</span></div>";
	}

	if (!empty($_COOKIE[session_name()]) && !empty($_SESSION['login'])) {
		$ffooter =
			"<div class='form__footer'>
			<p class='form__p'>
				Вы авторизованы, ваш логин: <strong>{$_SESSION['login']}</strong>
			</p>
			<p class='form__p'>
				Вы можете <a href='login.php' class='form__a'>выйти</a>!
			</p>
		</div>";
	} else {
		$ffooter =
			"<div class='form__footer'>
				<p class='form__p'>
					У вас уже есть аккаунт?
				</p>
				<p class='form__p'>
					Вы можете <a href='login.php' class='form__a'> войти</a>!
				</p>
			</div>";
	}

	$message = array();
	checkCookies('name', $message);
	checkCookies('email', $message);
	checkCookies('year', $message);
	checkCookies('gender', $message);
	checkCookies('numlimbs', $message);
	checkCookies('super-powers', $message);
	checkCookies('biography', $message);

	require_once("form.php");
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$requestError = false;
	if (!empty($_POST)) {
		if (empty($_POST["name"])) {
			$errors['name'] = "Введите имя!";
		} elseif (!preg_match("/^\s*[a-zA-Zа-яА-Я'][a-zA-Zа-яА-Я-' ]+[a-zA-Zа-яА-Я']?\s*$/u", $_POST["name"])) {
			$errors['name'] = "Несуществующее имя!";
		}

		if (empty($_POST["email"])) {
			$errors['email'] = "Введите e-mail!";
		} elseif (!preg_match("/^([a-z0-9_-]+\.)*[a-z0-9_-]+@[a-z0-9_-]+(\.[a-z0-9_-]+)*\.[a-z]{2,6}$/", $_POST["email"])) {
			$errors['email'] = "Несуществующий e-mail!";
		}

		if (empty($_POST["year"])) {
			$errors['year'] = "Выберите год!";
		} elseif (!preg_match("/^\s*[1]{1}9{1}\d{1}\d{1}.*$|^\s*200[0-8]{1}.*$/", $_POST["year"])) {
			$requestError = true;
		}

		if (!isset($_POST["gender"])) {
			$errors['gender'] = "Выберите пол!";
		} elseif (intval($_POST["gender"]) < 1 && 2 < intval($_POST["gender"])) {
			$requestError = true;
		}

		if (!isset($_POST["numlimbs"])) {
			$errors['numlimbs'] = "Выберите кол-во конечностей!";
		} elseif (intval($_POST["numlimbs"]) < 1 || 4 < intval($_POST["numlimbs"])) {
			$requestError = true;
		}

		if (!isset($_POST["super-powers"])) {
			$errors['super-powers'] = "Выберите хотя бы одну суперспособность!";
		} else {
			foreach ($_POST["super-powers"] as $value) {
				if (intval($value) < 1 || 3 < intval($value)) {
					$requestError = true;
					break;
				}
			}
		}

		if (empty($_POST["biography"])) {
			$errors['biography'] = "Расскажите что-нибудь о себе!";
		}
	} else {
		$requestError = true;
	}

	if ($requestError) {
		setcookie("request-error", '1', time() + 60 * 60 * 24);
		header("Location: index.php");
	} else {
		writeCookies('name', $errors);
		writeCookies('email', $errors);
		writeCookies('year', $errors);
		writeCookies('gender', $errors);
		writeCookies('numlimbs', $errors);
		writeCookies('biography', $errors);

		if (isset($errors['super-powers'])) {
			setcookie('super-powers-error', $errors['super-powers'], time() + 60 * 60 * 24);
		} else {
			$supPowers = array('1' => '0', '2' => '0', '3' => '0');
			foreach ($_POST['super-powers'] as $value) {
				$supPowers[$value] = '1';
			}
			foreach ($supPowers as $key => $value) {
				setcookie("super-powers[$key]", $value, time() + 60 * 60 * 24 * 365);
			}
		}
	}

	if (isset($errors)) {
		header("Location: index.php");
		exit();
	}

	$name = $_POST["name"];
	$email = $_POST["email"];
	$year = intval($_POST["year"]);
	$gender = $_POST["gender"];
	$limbs = intval($_POST["numlimbs"]);
	$superPowers = $_POST["super-powers"];
	$biography = $_POST["biography"];

	require_once("src/db.php");
	$db = new PDO("mysql:host=$dbServerName;dbname=$dbName", $dbUser, $dbPassword, array(PDO::ATTR_PERSISTENT => true));

	if (!empty($_COOKIE[session_name()]) && !empty($_SESSION['login'])) {
		$userId = intval($_SESSION['loginid']);

		try {
			$sql = "UPDATE user2 SET name = :name, email = :email, date = :date, gender = :gender, limbs = :limbs, biography = :biography WHERE id = :id";
			$stmt = $db->prepare($sql);
			$stmt->execute(array('id' => $userId, 'name' => $name, 'email' => $email, 'date' => $year, 'gender' => $gender, 'limbs' => $limbs, 'biography' => $biography));
		} catch (PDOException $e) {
			print('Error : ' . $e->getMessage());
			exit();
		}

		try {
			$sql = "DELETE FROM user_power2 WHERE id = :id";
			$stmt = $db->prepare($sql);
			$stmt->execute(array('id' => $userId));
		} catch (PDOException $e) {
			print('Error : ' . $e->getMessage());
			exit();
		}

		try {
			foreach ($superPowers as $value) {
				$stmt = $db->prepare("INSERT INTO user_power2 (id, power) VALUES (:id, :power)");
				$stmt->execute(array('id' => $userId, 'power' => intval($value)));
			}
		} catch (PDOException $e) {
			print('Error : ' . $e->getMessage());
			exit();
		}
		setcookie("update", '1', time() + 60 * 60 * 24);
	} else {
		$lastId = null;
		try {
			$stmt = $db->prepare("INSERT INTO user2 (name, email, date, gender, limbs, biography) VALUES (:name, :email, :date, :gender, :limbs, :biography)");
			$stmt->execute(array('name' => $name, 'email' => $email, 'date' => $year, 'gender' => $gender, 'limbs' => $limbs, 'biography' => $biography));
			$lastId = $db->lastInsertId();
		} catch (PDOException $e) {
			print('Error : ' . $e->getMessage());
			exit();
		}

		try {
			if ($lastId === null) {
				exit();
			}
			foreach ($superPowers as $value) {
				$stmt = $db->prepare("INSERT INTO user_power2 (id, power) VALUES (:id, :power)");
				$stmt->execute(array('id' => $lastId, 'power' => intval($value)));
			}
		} catch (PDOException $e) {
			print('Error : ' . $e->getMessage());
			exit();
		}

		$login =  "user$lastId";
		$password = gen_password();
		try {
			$stmt = $db->prepare("INSERT INTO user_authentication (id, login, password) VALUES (:id, :login, :password)");
			$stmt->execute(array('id' => $lastId, 'login' => $login, 'password' => password_hash($password, PASSWORD_DEFAULT)));
		} catch (PDOException $e) {
			print('Error : ' . $e->getMessage());
			exit();
		}

		setcookie('login', $login, time() + 60 * 60 * 24);
		setcookie('password', $password, time() + 60 * 60 * 24);
		setcookie("save", '1', time() + 60 * 60 * 24);
	}

	$db = null;


	header("Location: index.php");
}
