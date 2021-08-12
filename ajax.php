<?php
	include 'Inc/init.php';
	function start($link){
		session_start();
		if(($_GET['count']) == '9'){
			if($_SESSION['auth']){
				echo json_encode(profileUser($_SESSION['name'], $_SESSION['surname']));
			}else{
				echo json_encode(authContent());
			}
		}
		checkLinkGlobal($link);
		register($link);
		authentificate($link);
	}

	function getNav(){
		return "<nav>
					<li><a href data-value=\"profile\">Профиль</a></li>
					<li><a href data-value=\"messages\">Сообщения</a></li>
					<li><a href data-value=\"friends\">Друзья</a></li>
					<li><a href data-value=\"logout\">Выйти</a></li>
				</nav>";
	}

	function authContent($message = '', $login = '',$password = ''){
		if(!empty($message)){
			$message = "<div class=\"message\">$message</div>";
		}
		return ["$message
					<form>
						<input data-name=\"login\" value=\"$login\" placeholder=\"Логин\"> 
						<input type=\"password\" data-name=\"password\" value=\"$password\" placeholder=\"Пароль\"> 
						<input type=\"submit\" value=\"Войти\"  >
					</form>
					<a href data-value=\"reg\">Регистрация</a>
		", 'auth'];
	}

	function authentificate($link){
		if($_POST['count'] == 'auth'){
			$login = addInjectionString($link, 'login');
			$password = addInjectionString($link, 'password');
			$user = getOneFromDB($link, "SELECT * FROM users_data WHERE login='$login'");

			if($user){
				$hash = $user['password'];
				if(password_verify($password, $hash)){
					identifySession();
					echo json_encode(profileUser($_SESSION['name'], $_SESSION['surname']));
				}else{
					echo json_encode(authContent('Неправильно введен логин или пароль', $login, $password));
				}
			}else{
				echo json_encode(authContent('Неправильно введен логин или пароль', $login, $password));
			}

		}
	}

	function registerContent($message = '', $login = '', $password = '', $password_confirm = '', $name = '', $family = '', $birthday = ''){
			return ["$message
					<form>
						<input data-name=\"login\" value=\"$login\" placeholder=\"Логин\"> 
						<input type=\"password\" data-name=\"password\" value=\"$password\"  placeholder=\"Пароль\"> 
						<input type=\"password\" data-name=\"password_confirm\" value=\"$password_confirm\"  placeholder=\"Повтор пароля\"> 
						<input data-name=\"name\" value=\"$name\"  placeholder=\"Имя\"> 
						<input data-name=\"family\" value=\"$family\"  placeholder=\"Фамилия\"> 
						<input type=\"submit\" value=\"Зарегистрироваться\">
					</form>
					<a href data-value=\"auth\">Вход</a>
		", 'reg'];

	}

	function register($link){
		if($_POST['count'] == 'reg'){
			$login = addInjectionString($link, 'login');
			$password = password_hash(addInjectionString($link, 'password'), PASSWORD_DEFAULT);
			$password_confirm = addInjectionString($link, 'password_confirm');
			$name = addInjectionString($link, 'name');
			$family = addInjectionString($link, 'family');
			$birthday = addInjectionString($link, 'birthday');
			$user = getOneFromDB($link, "SELECT login FROM users_data WHERE login='$login'");
			if($user){
				echo json_encode(registerContent('Логин занят', $login, $password, $password_confirm, $name, $family, $birthday));
			}else{
				if(addInjectionString($link, 'password') == $password_confirm){
							identifySession();
							insertDb($link, "INSERT INTO users_data SET login='$login', password='$password', name='$name', family='$family'");
							echo json_encode(profileUser($name, $family));
				}else{
						echo json_encode(registerContent('Пароли не совпадают', $login, $password, $password_confirm, $name, $family, $birthday));
					}			
				}
		}
	}

	function profileUser($name, $surname){
		$nav = getNav();
		return ["$nav
				 <div class=\"prof\">
				 	<p class=\"nameUser\">$name $surname</p>
				  </div>", 
				 'profile'];
	}

	function messagesOfUser($link){
		$nav = getNav();
		$data = getAllFromDB($link, "SELECT * FROM users_messages WHERE from_user_id=$_SESSION[id] OR for_user_id=$_SESSION[id] GROUP BY dialog_id");
		$messages = '';
		foreach($data as $elem){
			$messages .= "<div class=\"messagesOfUser\"><a href data-value=\"dialog&id=$elem[dialog_id]\">$elem[message]</a></div>";
		}
		return ["$nav$messages", 'message'];
	}

	function windowDialog($link, $idDialog){
		$nav = getNav();
		$data = getAllFromDB($link, "SELECT users_data.name as name, users_messages.message as message FROM users_messages LEFT JOIN users_data ON users_messages.from_user_id=users_data.id WHERE dialog_id=$idDialog");
		$dialog = '';
		foreach($data as $elem){
			$dialog .= "<div class=\"message-col\">
							<p>$elem[name]</p><p>$elem[message]</p>
						</div>";
		}
		return ["$nav<div class=\"dialog\">$dialog</div><form><textarea data-name=\"message\"></textarea><input type=\"submit\"></form>
		", 'dialog'];
	}


	function sendMessage($link, $message, $dialog_id, $from_id, $for_id){
		insertDb($link, "INSERT INTO users_messages SET for_user_id=$for_id, message='$message', from_user_id=$for_id, dialog_id=$dialog_id");
		echo json_encode(windowDialog($link, $dialog_id));
	}


	function checkLink($value){
		return $_POST['link'] == $value;
	}

	function checkLinkGlobal($link){
		if(checkLink('reg')){
			echo json_encode(registerContent());
		}
		if(checkLink('auth')){
			echo json_encode(authContent());
		}
		if(checkLink('messages')){
			echo json_encode(messagesOfUser($link));
		}
		if(checkLink('dialog')){
			echo json_encode(windowDialog($link, $_POST['id']));
		}
		if(checkLink('profile')){
			echo json_encode(profileUser($_SESSION['name'], $_SESSION['surname']));
		}
		if(checkLink('message')){
			sendMessage($link, $_POST['message'], 1,1,1);
		}
		if(checkLink('logout')){
			logout();
		}
	}


	function getAllFromDB($link, $query){
		$result = mysqli_query($link, $query) or die(mysqli_error($link));
		for($data = []; $row = mysqli_fetch_assoc($result); $data[] = $row);
		return $data;	
	}

	function insertDb($link, $query){
		mysqli_query($link, $query) or die(mysqli_error($link));
	}

	function getOneFromDB($link, $query){
		$result = mysqli_query($link, $query) or die(mysqli_error($link));
		$user = mysqli_fetch_assoc($result);
		return $user;
	}


	function addInjectionString($link, $name){
		return htmlspecialchars(trim(mysqli_real_escape_string($link, $_POST[$name])));
	}


	function identifySession(){
		$_SESSION['name'] = $user['name'];
		$_SESSION['surname'] = $user['family'];
		$_SESSION['id'] = $user['id'];
		$_SESSION['auth'] = true;
	}

	function logout(){
		$_SESSION['name'] = null;
		$_SESSION['surname'] = null;
		$_SESSION['id'] = null;
		$_SESSION['auth'] = null;
		echo json_encode(authContent());
	}

	start($link);
?>