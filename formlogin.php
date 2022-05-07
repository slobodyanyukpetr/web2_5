<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="shortcut icon" href="#" type="image/x-icon">
	<link rel="stylesheet" href="css/style.css">
	<title>Войдите</title>
</head>

<body>

	<div class="form">
		<?php
		echo $loginHeader;
		?>
		<form class="form__body" action=".\login.php" method="post">
			<div class="form__item">
				<label class="form__label">
					<input class="form__input form__input_text" placeholder="Логин" type="text" name="login">
				</label>
				<?php echo $message['login-error']; ?>
			</div>
			<div class="form__item">
				<label class="form__label">
					<input class="form__input form__input_text" placeholder="Пароль" type="password" name="password">
				</label>
				<?php echo $message['password-error']; ?>
			</div>
			<div class="form__item form__item_submit">
				<label class="form__label">
					<input class="form__submit" type="submit" value="Войти">
				</label>
			</div>
		</form>
	</div>
</body>

</body>

</html>