<?php
// ========== begin WEBAPP konfigurace
// zavedeni session
session_start();
// zakazani cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");
// zakazani timezone
date_default_timezone_set('Europe/Prague');
// zakazani error reportu
error_reporting(0);
mysqli_report(MYSQLI_REPORT_OFF);
// ziskani url a aplikace htmlspecialchars
$url = htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, 'UTF-8');
// blacklist slov v url
$wrong_words_url_array = ['meritaccess', '/', 'index.php', '?'];
// cisteni url
$clean_url = str_replace($wrong_words_url_array, '', $url);
// prevod aktualniho casu na unix format
$act_datetime_unix = strtotime(date('Y-m-d H:i:s'));
// hledany vyraz #0
$search_definition_0 = 'v=';
// pole pohledu
$stat_view_array = ['a' => 'access', 'c' => 'cards', 'g' => 'readers', 'h' => 'home', 'l' => 'lock', 'm' => 'more', 'o' => 'config', 'r' => 'running', 't' => 'timezones', 'u' => 'users', 'v' => 'logs', 'x' => 'drop'];
$stat_cond_array = ['b' => 'block', 'd' => 'delete', 'e' => 'edit', 'p' => 'past', 'r' => 'reboot', 's' => 'resync'];
$stat_info_array = ['o' => 'order'];
$stat_fact_array = ['z' => 'alert'];
// titulky v title
$title_view_array = ['access' => 'Access', 'cards' => 'Cards', 'config' => 'Configuration', 'home' => 'Dashboard', 'logs' => 'Logs', 'lock' => 'Login', 'more' => 'More', 'readers' => 'Readers', 'running' => 'Running', 'timezones' => 'Timezones', 'users' => 'Users'];
// pole vyloucenych, ktere musi mit login
$keys_url_array_except = [$stat_view_array['a'], $stat_view_array['c'], $stat_view_array['g'], $stat_view_array['h'], $stat_view_array['m'], $stat_view_array['o'], $stat_view_array['r'], $stat_view_array['t'], $stat_view_array['u'], $stat_view_array['v']];
// pokud chybi v url, doplni
if (strpos($url, $search_definition_0) === false) {
	if (!isset($_SESSION['access_granted'])) {
		header('location: ./?v=lock');
		exit;
	} else {
		header('location: ./?v=home');
		exit;
	}
}
// roztrhani url
$temp_url = explode(';', $clean_url);
// parsovani url
foreach ($temp_url as $pair_url) {
	list($value_url, $key_url) = explode('=', $pair_url);
	$part_url[htmlspecialchars($value_url, ENT_QUOTES, 'UTF-8')] = htmlspecialchars($key_url, ENT_QUOTES, 'UTF-8');
}
// klic pohledu
$key_view = strval($part_url['v'] ?? "");
$key_cond = strval($part_url['s'] ?? "");
$key_info = strval($part_url['i'] ?? "");
$key_fact = strval($part_url['f'] ?? "");
// presmerovani pokud v adrese jsou jine stavy nez definovane
if (!in_array($key_view, $stat_view_array)) {
	if (!isset($_SESSION['access_granted'])) {
		header('location: ./?v=lock');
		exit;
	} else {
		header('location: ./?v=home');
		exit;
	}
}
// presmerovani pokud neni uzivatel prihlasen
if (!isset($_SESSION['access_granted'])) {
	if (in_array($key_view, $keys_url_array_except)) {
		header('location: ./?v=lock');
		exit;
	}
}
// funkce pro verzovani
function fileVersioning($tracked_file_input) {
	$datetime_update = date('ymdHi', filemtime($tracked_file_input));
	$tracked_file_output = htmlspecialchars($tracked_file_input, ENT_QUOTES, 'UTF-8') . '?v=' . $datetime_update;
	return $tracked_file_output;
}
// ziskani casu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_time') {
	echo date('d.m.Y, H:i:s');
	exit;
}
// ziskani casu - typ 2
$server_time = date('Y-m-d H:i:s');
// datum aktualizace - index
$update_datetime = filemtime(__FILE__);
// ========== end WEBAPP konfigurace
// ========== begin MYSQL pripojeni
// definice konstant pro pripojeni k databazi
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'ma');
define('DB_PASSWORD', 'FrameWork5414*');
define('DB_NAME', 'MeritAccessLocal');
// pripojeni k databazi
$connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
// pokud nefunguje spojeni - chyba
if ($connection === false) {
	die('Connection error! ' . htmlspecialchars(mysqli_connect_error(), ENT_QUOTES, 'UTF-8'));
}
// ========== end MYSQL pripojeni
// ========== begin LOGIN
if (isset($_POST['login'])) {
// vstupy z formulare
	$input_username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
	$input_password = htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8');
// pripravene dotazy pro ochranu proti sql injection
	$stmt = $connection->prepare("SELECT * FROM users WHERE LogonName = ? AND MD5 = MD5(?) AND rights = 255");
	$stmt->bind_param("ss", $input_username, $input_password);
	$stmt->execute();
	$result = $stmt->get_result();
	if ($result->num_rows == 1) {
		$_SESSION['access_granted'] = true;
		header('Location: ./?v=home');
		exit;
	} else {
// kontrola pro neplatne prava
		$stmt_rights = $connection->prepare("SELECT rights FROM users WHERE LogonName = ?");
		$stmt_rights->bind_param("s", $input_username);
		$stmt_rights->execute();
		$result_rights = $stmt_rights->get_result();
		if ($result_rights->num_rows == 1) {
			$row_rights = $result_rights->fetch_assoc();
			if ($row_rights['rights'] != 255) {
				$_SESSION['error_message'] = 'User does not have sufficient rights!';
				header('Location: ./?v=lock;f=alert');
				exit;
			} else {
				$_SESSION['error_message'] = 'Wrong username or password!';
				header('Location: ./?v=lock;f=alert');
				exit;
			}
		} else {
			$_SESSION['error_message'] = 'Error getting user rights!';
			header('Location: ./?v=lock;f=alert');
			exit;
		}
	}
}
// odhlaseni
if ($key_view == $stat_view_array['x']) {
	session_destroy();
	header('Location: ./?v=lock');
	exit;
}
// ========== end LOGIN
// ========== begin CARDS
// novy zaznam karty
if ($key_view == $stat_view_array['c'] && $key_cond == $stat_cond_array['p'] && $key_cond != $stat_cond_array['e'] && $key_cond != $stat_cond_array['d']) {
// ulozeni dat karty do db
	if (isset($_POST['pridat-karta'])) {
// ziskani dat z post
		$nova_karta = htmlspecialchars($_POST['nova-karta'], ENT_QUOTES, 'UTF-8');
		$nova_ctecka = htmlspecialchars($_POST['nova-ctecka'], ENT_QUOTES, 'UTF-8');
		$novy_cas_plan = htmlspecialchars($_POST['novy-cas-plan'], ENT_QUOTES, 'UTF-8');
		$nove_povoleni = htmlspecialchars($_POST['nove-povoleni'], ENT_QUOTES, 'UTF-8');
		$nove_smazano = htmlspecialchars($_POST['nove-smazano'], ENT_QUOTES, 'UTF-8');
		$nove_pozn = htmlspecialchars($_POST['nove-pozn'], ENT_QUOTES, 'UTF-8');
// priprava sql dotazu
		$sqlquery_novy_zaznam = "INSERT INTO Karty (Karta, Ctecka, CasPlan, Povoleni, Smazano, Pozn) VALUES (?, ?, ?, ?, ?, ?)";
		$stmt = mysqli_prepare($connection, $sqlquery_novy_zaznam);
		if (!$stmt) {
// chyba pri priprave dotazu
			$_SESSION['error_message'] = 'Error preparing query: ' . htmlspecialchars(mysqli_error($connection), ENT_QUOTES, 'UTF-8');
			header('Location: ./?v=cards;f=alert');
			exit;
		}
// bindovani parametru
		mysqli_stmt_bind_param($stmt, "ssssss", $nova_karta, $nova_ctecka, $novy_cas_plan, $nove_povoleni, $nove_smazano, $nove_pozn);
// vykonani dotazu
		if (mysqli_stmt_execute($stmt)) {
			header('Location: ./?v=cards');
			exit;
		} else {
// chyba pri vykonani dotazu
			$_SESSION['error_message'] = 'Error creating record: ' . htmlspecialchars(mysqli_stmt_error($stmt), ENT_QUOTES, 'UTF-8');
			header('Location: ./?v=cards;f=alert');
			exit;
		}
// uzavreni statementu
		mysqli_stmt_close($stmt);
	}
}
// editace karty
if ($key_view == $stat_view_array['c'] && $key_cond != $stat_cond_array['p'] && $key_cond == $stat_cond_array['e'] && $key_cond != $stat_cond_array['d']) {
// nacteni dat karty do formulare
	$cardid = $key_info;
	$sqlquery_select = "SELECT * FROM Karty WHERE cardid = ?";
	$stmt = mysqli_prepare($connection, $sqlquery_select);
	mysqli_stmt_bind_param($stmt, "i", $cardid);
	if (mysqli_stmt_execute($stmt)) {
		$result_select = mysqli_stmt_get_result($stmt);
		if (mysqli_num_rows($result_select) > 0) {
			$row_select = mysqli_fetch_array($result_select);
		} else {
			$_SESSION['error_message'] = 'Record not found!';
			header('Location: ./?v=cards;f=alert');
			exit;
		}
	} else {
		$_SESSION['error_message'] = 'Error executing the query: ' . htmlspecialchars(mysqli_error($connection), ENT_QUOTES, 'UTF-8') . '!';
		header('Location: ./?v=cards;f=alert');
		exit;
	}
// editace zaznamu karty do db
	if (isset($_POST['edit-zaznam'])) {
		$cardid = htmlspecialchars($_POST['cardid'], ENT_QUOTES, 'UTF-8');
		$edit_karta = htmlspecialchars($_POST['edit-karta'], ENT_QUOTES, 'UTF-8');
		$edit_ctecka = htmlspecialchars($_POST['edit-ctecka'], ENT_QUOTES, 'UTF-8');
		$edit_cas_plan = htmlspecialchars($_POST['edit-cas-plan'], ENT_QUOTES, 'UTF-8');
		$edit_povoleni = htmlspecialchars($_POST['edit-povoleni'], ENT_QUOTES, 'UTF-8');
		$edit_smazano = htmlspecialchars($_POST['edit-smazano'], ENT_QUOTES, 'UTF-8');
		$edit_pozn = htmlspecialchars($_POST['edit-pozn'], ENT_QUOTES, 'UTF-8');
		$sqlquery_edit = "UPDATE Karty SET Karta = ?, Ctecka = ?, CasPlan = ?, Povoleni = ?, Smazano = ?, Pozn = ? WHERE cardid = ?";
		$stmt = mysqli_prepare($connection, $sqlquery_edit);
		if (!$stmt) {
			$_SESSION['error_message'] = 'Error preparing query: ' . htmlspecialchars(mysqli_error($connection), ENT_QUOTES, 'UTF-8');
			header('Location: ./?v=cards;f=alert');
			exit;
		}
		mysqli_stmt_bind_param($stmt, "ssssssi", $edit_karta, $edit_ctecka, $edit_cas_plan, $edit_povoleni, $edit_smazano, $edit_pozn, $cardid);
		if (mysqli_stmt_execute($stmt)) {
			header('Location: ./?v=cards');
			exit;
		} else {
			$_SESSION['error_message'] = 'Error updating record: ' . htmlspecialchars(mysqli_error($connection), ENT_QUOTES, 'UTF-8') . '!';
			header('Location: ./?v=cards;f=alert');
			exit;
		}
		mysqli_stmt_close($stmt);
	}
}
// mazani karty
if ($key_view == $stat_view_array['c'] && $key_cond != $stat_cond_array['p'] && $key_cond != $stat_cond_array['e'] && $key_cond == $stat_cond_array['d']) {
// smazani zaznamu karty
	$cardid = $key_info;
	$sqlquery_delete = "UPDATE Karty SET Smazano = 1 WHERE cardid = ?";
	$stmt = mysqli_prepare($connection, $sqlquery_delete);
	mysqli_stmt_bind_param($stmt, "i", $cardid);
	if (mysqli_stmt_execute($stmt)) {
		header('Location: ./?v=cards');
		exit;
	} else {
		$_SESSION['error_message'] = 'Error deleting record: ' . htmlspecialchars(mysqli_error($connection), ENT_QUOTES, 'UTF-8') . '!';
		header('Location: ./?v=cards;f=alert');
		exit;
	}
	mysqli_stmt_close($stmt);
}
// ========== end CARDS
// ========== begin USERS
// prace s uzivatelem
if ($key_view == $stat_view_array['u'] && $key_cond == $stat_cond_array['p'] && $key_cond != $stat_cond_array['e'] && $key_cond != $stat_cond_array['d']) {
// nový zaznam uzivatele
	if (isset($_POST['novy-uzivatel'])) {
		$novy_user = htmlspecialchars($_POST['novy-user'], ENT_QUOTES, 'UTF-8');
		$plain_password = $_POST['novy-md5'];
		$novy_md5 = md5($plain_password);
		$nova_prava = htmlspecialchars($_POST['nova-prava'], ENT_QUOTES, 'UTF-8');
		$sqlquery_novy_zaznam = "INSERT INTO users (LogonName, MD5, rights) VALUES (?, ?, ?)";
		$stmt = mysqli_prepare($connection, $sqlquery_novy_zaznam);
		if ($stmt) {
			mysqli_stmt_bind_param($stmt, "sss", $novy_user, $novy_md5, $nova_prava);
			if (mysqli_stmt_execute($stmt)) {
				header('Location: ./?v=users');
				exit;
			} else {
				$_SESSION['error_message'] = 'Error creating user: ' . mysqli_stmt_error($stmt);
				header('Location: ./?v=users;f=alert');
				exit;
			}
		} else {
			$_SESSION['error_message'] = 'Error preparing the statement: ' . mysqli_error($connection);
			header('Location: ./?v=users;f=alert');
			exit;
		}
	}
}
// editace uzivatele
if ($key_view == $stat_view_array['u'] && $key_cond != $stat_cond_array['p'] && $key_cond == $stat_cond_array['e'] && $key_cond != $stat_cond_array['d']) {
// nacteni dat uzivatele do formulare
	$id = intval($key_info);
	$sqlquery_select = "SELECT * FROM users WHERE id = ?";
	$stmt = mysqli_prepare($connection, $sqlquery_select);
	if ($stmt) {
		mysqli_stmt_bind_param($stmt, "i", $id);
		if (mysqli_stmt_execute($stmt)) {
			$result_select = mysqli_stmt_get_result($stmt);
			if (mysqli_num_rows($result_select) > 0) {
				$row_select = mysqli_fetch_array($result_select);
			} else {
				$_SESSION['error_message'] = 'User not found!';
				header('Location: ./?v=users;f=alert');
				exit;
			}
		} else {
			$_SESSION['error_message'] = 'Error executing the query: ' . mysqli_stmt_error($stmt);
			header('Location: ./?v=users;f=alert');
			exit;
		}
	} else {
		$_SESSION['error_message'] = 'Error preparing the statement: ' . mysqli_error($connection);
		header('Location: ./?v=users;f=alert');
		exit;
	}
// editace zaznamu uzivatele
	if (isset($_POST['edit-uzivatel'])) {
		$id = intval($_POST['id']);
		$edit_user = htmlspecialchars($_POST['edit-user'], ENT_QUOTES, 'UTF-8');
		$edit_plain_password = $_POST['edit-md5'];
		$edit_md5 = md5($edit_plain_password);
		$edit_prava = htmlspecialchars($_POST['edit-prava'], ENT_QUOTES, 'UTF-8');
		$sqlquery_edit = "UPDATE users SET LogonName = ?, MD5 = ?, rights = ? WHERE id = ?";
		$stmt = mysqli_prepare($connection, $sqlquery_edit);
		if ($stmt) {
			mysqli_stmt_bind_param($stmt, "sssi", $edit_user, $edit_md5, $edit_prava, $id);
			if (mysqli_stmt_execute($stmt)) {
				header('Location: ./?v=users');
				exit;
			} else {
				$_SESSION['error_message'] = 'Error updating record: ' . mysqli_stmt_error($stmt);
				header('Location: ./?v=users;f=alert');
				exit;
			}
		} else {
			$_SESSION['error_message'] = 'Error preparing the statement: ' . mysqli_error($connection);
			header('Location: ./?v=users;f=alert');
			exit;
		}
	}
}
// mazani uzivatele
if ($key_view == $stat_view_array['u'] && $key_cond != $stat_cond_array['p'] && $key_cond != $stat_cond_array['e'] && $key_cond == $stat_cond_array['d']) {
// smazani zaznamu uzivatele
	$id = intval($key_info);
	$sqlquery_delete = "UPDATE users SET rights = 0 WHERE id = ?";
	$stmt = mysqli_prepare($connection, $sqlquery_delete);
	if ($stmt) {
		mysqli_stmt_bind_param($stmt, "i", $id);
		if (mysqli_stmt_execute($stmt)) {
			header('Location: ./?v=users');
			exit;
		} else {
			$_SESSION['error_message'] = 'Error blocking user: ' . mysqli_stmt_error($stmt);
			header('Location: ./?v=users;f=alert');
			exit;
		}
	} else {
		$_SESSION['error_message'] = 'Error preparing the statement: ' . mysqli_error($connection);
		header('Location: ./?v=users;f=alert');
		exit;
	}
}
// ========== end USERS
// ========== begin CONFIGDU
// editace konfigurace
if ($key_view == $stat_view_array['o'] && $key_cond == $stat_cond_array['e']) {
	$config_data = array();
	$config_data_temp = array();
// editace zaznamu konfigurace
	if (isset($_POST['edit-config'])) {
// pokud existuje session, presune ji do temp_session
		if (!isset($_SESSION['config_data_temp']) && isset($_SESSION['config_data'])) {
			$_SESSION['config_data_temp'] = $_SESSION['config_data'];
		}
		$id = htmlspecialchars($_POST['id'], ENT_QUOTES, 'UTF-8');
		$edit_property = htmlspecialchars($_POST['edit-property'], ENT_QUOTES, 'UTF-8');
		$edit_value = htmlspecialchars($_POST['edit-value'], ENT_QUOTES, 'UTF-8');
		$configdu_valid = htmlspecialchars($_POST['configdu-valid'], ENT_QUOTES, 'UTF-8');
		if (!preg_match('/' . $configdu_valid . '/', $edit_value)) {
			$_SESSION['error_message'] = 'Error! Invalid format for property: ' . $edit_property . '!';
			header('Location: ./?v=config;f=alert');
			exit;
		} else {
// najde index daneho id v poli session
			$index = array_search($id, array_column($_SESSION['config_data_temp'], 'id'));
// pokud index byl nalezen
			if ($index !== false) {
// aktualizuje data v session
				$_SESSION['config_data_temp'][$index]['property'] = $edit_property;
				$_SESSION['config_data_temp'][$index]['value'] = $edit_value;
			} else {
// pokud index nebyl nalezen, prida novy zaznam
				$_SESSION['config_data_temp'][] = array('id' => $id, 'property' => $edit_property, 'value' => $edit_value);
			}
			header('Location: ./?v=config');
			exit;
		}
	}
}
// editace konfigurace - ulozeni
if ($key_view == $stat_view_array['o'] && $key_cond == $stat_cond_array['p']) {
	$sqlquery_edit = "UPDATE ConfigDU SET property = ?, value = ? WHERE id = ?";
	$stmt = mysqli_prepare($connection, $sqlquery_edit);
	if ($stmt === false) {
		$_SESSION['error_message'] = 'Error in preparing sql: ' . htmlspecialchars(mysqli_error($connection), ENT_QUOTES, 'UTF-8') . '!';
		header('Location: ./?v=config;f=alert');
		exit;
	}
// projde vsechny zaznamy v session
	if (isset($_SESSION['config_data_temp'])) {
		foreach ($_SESSION['config_data_temp'] as $data) {
			$id = $data['id'];
			$edit_property = $data['property'];
			$edit_value = $data['value'];
// priprava zaznamu
			mysqli_stmt_bind_param($stmt, "ssi", $edit_property, $edit_value, $id);
			$result_edit = mysqli_stmt_execute($stmt);
// pokud doslo k chybe, vypise ji
			if (!$result_edit) {
				$_SESSION['error_message'] = 'Error updating database: ' . htmlspecialchars(mysqli_error($connection), ENT_QUOTES, 'UTF-8') . '!';
				header('Location: ./?v=config;f=alert');
				exit;
			}
		}
		unset($_SESSION['config_data_temp']);
	}
// aktualizace running
	$sqlquery_update_running = "UPDATE running SET value = 1 WHERE property = 'restart' AND value = 0";
	$result_update_running = mysqli_query($connection, $sqlquery_update_running);
	if (!$result_update_running) {
		$_SESSION['error_message'] = 'Error updating running table: ' . htmlspecialchars(mysqli_error($connection), ENT_QUOTES, 'UTF-8') . '!';
		header('Location: ./?v=config;f=alert');
		exit;
	}
// presmerovani
	header('Location: ./?v=config');
	exit;
}
// ========== end CONFIGDU
// ========== begin TIMEZONES
// funkce pro pripravu a provedeni dotazu
function execute_query($connection, $query, $params, $types) {
	$stmt = mysqli_prepare($connection, $query);
	if (!$stmt) {
// error query
		$_SESSION['error_message'] = 'Error preparing query: ' . htmlspecialchars(mysqli_error($connection), ENT_QUOTES, 'UTF-8');
		header('location: ./?v=timezones;f=alert');
		exit;
	}
	mysqli_stmt_bind_param($stmt, $types, ...$params);
	if (mysqli_stmt_execute($stmt)) {
		return $stmt;
	} else {
// error query
		$_SESSION['error_message'] = 'Error executing query: ' . htmlspecialchars(mysqli_stmt_error($stmt), ENT_QUOTES, 'UTF-8');
		header('location: ./?v=timezones;f=alert');
		exit;
	}
}
// funkce pro ziskani pouzitych cisel zony
function get_used_zone_numbers($connection) {
	$query = "SELECT Cislo FROM CasovePlany";
	$result = mysqli_query($connection, $query);
	$used_numbers = [];
	while ($row = mysqli_fetch_assoc($result)) {
		$used_numbers[] = htmlspecialchars($row['Cislo'], ENT_QUOTES, 'UTF-8');
	}
	return $used_numbers;
}
// funkce pro ziskani volnych cisel zony
function get_free_zone_numbers($connection) {
	$used_numbers = get_used_zone_numbers($connection);
	$all_numbers = range(1, 62);
	$free_numbers = array_diff($all_numbers, $used_numbers);
	return $free_numbers;
}
// funkce pro ziskani nazvu zony
function get_zone_name_by_number($connection, $zone_number) {
	$query = "SELECT Nazev FROM CasovePlany WHERE Cislo = ?";
	$stmt = mysqli_prepare($connection, $query);
	mysqli_stmt_bind_param($stmt, 'i', $zone_number);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_bind_result($stmt, $zone_name);
	mysqli_stmt_fetch($stmt);
	mysqli_stmt_close($stmt);
	return htmlspecialchars($zone_name, ENT_QUOTES, 'UTF-8');
}
// novy zaznam casove zony
if ($key_view == $stat_view_array['t'] && $key_cond == $stat_cond_array['p'] && $key_cond != $stat_cond_array['e'] && $key_cond != $stat_cond_array['d']) {
	if (isset($_POST['nova-zona'])) {
// ziskani dat z POST
		$nove_cislo = htmlspecialchars(mysqli_real_escape_string($connection, $_POST['nove-cislo']), ENT_QUOTES, 'UTF-8');
		$novy_nazev = htmlspecialchars(mysqli_real_escape_string($connection, $_POST['novy-nazev']), ENT_QUOTES, 'UTF-8');
		$novy_popis = htmlspecialchars(mysqli_real_escape_string($connection, $_POST['novy-popis']), ENT_QUOTES, 'UTF-8');
		$novy_stav = htmlspecialchars(mysqli_real_escape_string($connection, $_POST['novy-stav']), ENT_QUOTES, 'UTF-8');
		$times = ['mo' => [$_POST['mostart1v'], $_POST['moend1v'], $_POST['mostart2v'], $_POST['moend2v']], 'tu' => [$_POST['tustart1v'], $_POST['tuend1v'], $_POST['tustart2v'], $_POST['tuend2v']], 'we' => [$_POST['westart1v'], $_POST['weend1v'], $_POST['westart2v'], $_POST['weend2v']], 'th' => [$_POST['thstart1v'], $_POST['thend1v'], $_POST['thstart2v'], $_POST['thend2v']], 'fr' => [$_POST['frstart1v'], $_POST['frend1v'], $_POST['frstart2v'], $_POST['frend2v']], 'sa' => [$_POST['sastart1v'], $_POST['saend1v'], $_POST['sastart2v'], $_POST['saend2v']], 'su' => [$_POST['sustart1v'], $_POST['suend1v'], $_POST['sustart2v'], $_POST['suend2v']], 'ho' => [$_POST['hostart1v'], $_POST['hoend1v'], $_POST['hostart2v'], $_POST['hoend2v']]];
		$times_flat = [];
		foreach ($times as $day_times) {
			foreach ($day_times as $time) {
				$times_flat[] = htmlspecialchars(mysqli_real_escape_string($connection, $time), ENT_QUOTES, 'UTF-8');
			}
		}
// priprava SQL dotazu
		$sqlquery_novy_zaznam = "INSERT INTO CasovePlany (Cislo, Nazev, Popis, RezimOtevirani, Po_PrvniZacatek, Po_PrvniKonec, Po_DruhyZacatek, Po_DruhyKonec, Ut_PrvniZacatek, Ut_PrvniKonec, Ut_DruhyZacatek, Ut_DruhyKonec, St_PrvniZacatek, St_PrvniKonec, St_DruhyZacatek, St_DruhyKonec, Ct_PrvniZacatek, Ct_PrvniKonec, Ct_DruhyZacatek, Ct_DruhyKonec, Pa_PrvniZacatek, Pa_PrvniKonec, Pa_DruhyZacatek, Pa_DruhyKonec, So_PrvniZacatek, So_PrvniKonec, So_DruhyZacatek, So_DruhyKonec, Ne_PrvniZacatek, Ne_PrvniKonec, Ne_DruhyZacatek, Ne_DruhyKonec, Svatky_PrvniZacatek, Svatky_PrvniKonec, Svatky_DruhyZacatek, Svatky_DruhyKonec) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$params = array_merge([$nove_cislo, $novy_nazev, $novy_popis, $novy_stav], $times_flat);
		$types = str_repeat('s', count($params));
		execute_query($connection, $sqlquery_novy_zaznam, $params, $types);
		header('location: ./?v=timezones');
		exit;
	}
}
// editace casove zony
if ($key_view == $stat_view_array['t'] && $key_cond != $stat_cond_array['p'] && $key_cond == $stat_cond_array['e'] && $key_cond != $stat_cond_array['d']) {
	$tzid = htmlspecialchars(mysqli_real_escape_string($connection, $key_info), ENT_QUOTES, 'UTF-8');
	$sqlquery_select = "SELECT * FROM CasovePlany WHERE Id_CasovyPlan = ?";
	$stmt = execute_query($connection, $sqlquery_select, [$tzid], 'i');
	$result_select = mysqli_stmt_get_result($stmt);
	if (mysqli_num_rows($result_select) > 0) {
		$row_select = mysqli_fetch_array($result_select);
	} else {
// record not found
		$_SESSION['error_message'] = 'Record not found!';
		header('location: ./?v=timezones;f=alert');
		exit;
	}
	if (isset($_POST['edit-zona'])) {
		$id = htmlspecialchars(mysqli_real_escape_string($connection, $_POST['id']), ENT_QUOTES, 'UTF-8');
		$edit_cislo = htmlspecialchars(mysqli_real_escape_string($connection, $_POST['edit-cislo']), ENT_QUOTES, 'UTF-8');
		$edit_nazev = htmlspecialchars(mysqli_real_escape_string($connection, $_POST['edit-nazev']), ENT_QUOTES, 'UTF-8');
		$edit_popis = htmlspecialchars(mysqli_real_escape_string($connection, $_POST['edit-popis']), ENT_QUOTES, 'UTF-8');
		$edit_stav = htmlspecialchars(mysqli_real_escape_string($connection, $_POST['edit-stav']), ENT_QUOTES, 'UTF-8');
		$times = ['mo' => [$_POST['mostart1v'], $_POST['moend1v'], $_POST['mostart2v'], $_POST['moend2v']], 'tu' => [$_POST['tustart1v'], $_POST['tuend1v'], $_POST['tustart2v'], $_POST['tuend2v']], 'we' => [$_POST['westart1v'], $_POST['weend1v'], $_POST['westart2v'], $_POST['weend2v']], 'th' => [$_POST['thstart1v'], $_POST['thend1v'], $_POST['thstart2v'], $_POST['thend2v']], 'fr' => [$_POST['frstart1v'], $_POST['frend1v'], $_POST['frstart2v'], $_POST['frend2v']], 'sa' => [$_POST['sastart1v'], $_POST['saend1v'], $_POST['sastart2v'], $_POST['saend2v']], 'su' => [$_POST['sustart1v'], $_POST['suend1v'], $_POST['sustart2v'], $_POST['suend2v']], 'ho' => [$_POST['hostart1v'], $_POST['hoend1v'], $_POST['hostart2v'], $_POST['hoend2v']]];
		$times_flat = [];
		foreach ($times as $day_times) {
			foreach ($day_times as $time) {
				$times_flat[] = htmlspecialchars(mysqli_real_escape_string($connection, $time), ENT_QUOTES, 'UTF-8');
			}
		}
// priprava SQL dotazu
		$sqlquery_edit_zaznam = "UPDATE CasovePlany SET Cislo = ?, Nazev = ?, Popis = ?, RezimOtevirani = ?, Po_PrvniZacatek = ?, Po_PrvniKonec = ?, Po_DruhyZacatek = ?, Po_DruhyKonec = ?, Ut_PrvniZacatek = ?, Ut_PrvniKonec = ?, Ut_DruhyZacatek = ?, Ut_DruhyKonec = ?, St_PrvniZacatek = ?, St_PrvniKonec = ?, St_DruhyZacatek = ?, St_DruhyKonec = ?, Ct_PrvniZacatek = ?, Ct_PrvniKonec = ?, Ct_DruhyZacatek = ?, Ct_DruhyKonec = ?, Pa_PrvniZacatek = ?, Pa_PrvniKonec = ?, Pa_DruhyZacatek = ?, Pa_DruhyKonec = ?, So_PrvniZacatek = ?, So_PrvniKonec = ?, So_DruhyZacatek = ?, So_DruhyKonec = ?, Ne_PrvniZacatek = ?, Ne_PrvniKonec = ?, Ne_DruhyZacatek = ?, Ne_DruhyKonec = ?, Svatky_PrvniZacatek = ?, Svatky_PrvniKonec = ?, Svatky_DruhyZacatek = ?, Svatky_DruhyKonec = ? WHERE Id_CasovyPlan = ?";
		$params = array_merge([$edit_cislo, $edit_nazev, $edit_popis, $edit_stav], $times_flat, [$id]);
		$types = str_repeat('s', count($params) - 1) . 'i';
		execute_query($connection, $sqlquery_edit_zaznam, $params, $types);
		header('location: ./?v=timezones');
		exit;
	}
}
// ========== end TIMEZONES
// ========== begin READERS
// prace se cteckou
if ($key_view == $stat_view_array['g'] && $key_cond == $stat_cond_array['e']) {
// nacteni dat ctecky do formulare
	$id = $key_info;
	$sqlquery_select = "SELECT * FROM Readers WHERE id = ?";
	$stmt = mysqli_prepare($connection, $sqlquery_select);
	if ($stmt) {
		mysqli_stmt_bind_param($stmt, "i", $id);
		if (mysqli_stmt_execute($stmt)) {
			$result_select = mysqli_stmt_get_result($stmt);
			if (mysqli_num_rows($result_select) > 0) {
				$row_select = mysqli_fetch_array($result_select, MYSQLI_ASSOC);
			} else {
				$_SESSION['error_message'] = 'Reader not found!';
				header('Location: ./?v=readers;f=alert');
				exit;
			}
		} else {
			$_SESSION['error_message'] = 'Error executing the query: ' . mysqli_error($connection) . '!';
			header('Location: ./?v=readers;f=alert');
			exit;
		}
// uzavreni prepared statementu po pouziti
		mysqli_stmt_close($stmt);
	} else {
		$_SESSION['error_message'] = 'Error preparing the statement: ' . mysqli_error($connection) . '!';
		header('Location: ./?v=readers;f=alert');
		exit;
	}
// uprava zaznamu ctecky
	if (isset($_POST['edit-ctecka'])) {
		$id = $_POST['id'];
		$edit_outputu = $_POST['edit-outputu'];
		$edit_pulzu = $_POST['edit-pulzu'];
		$edit_cas_planu = $_POST['edit-cas-planu'];
		$edit_monitor = $_POST['edit-monitor'];
		$edit_monitordef = $_POST['edit-monitordef'];
		$edit_opentime = $_POST['edit-opentime'];
		$edit_hasmonitor = $_POST['edit-hasmonitor'];
		$sqlquery_edit = "UPDATE Readers SET output = ?, pulse_time = ?, sys_plan = ?, monitor = ?, monitor_default = ?, max_open_time = ?, has_monitor = ? WHERE id = ?";
		$stmt = mysqli_prepare($connection, $sqlquery_edit);
		if ($stmt) {
			mysqli_stmt_bind_param($stmt, "sssssssi", $edit_outputu, $edit_pulzu, $edit_cas_planu, $edit_monitor, $edit_monitordef, $edit_opentime, $edit_hasmonitor, $id);
			if (mysqli_stmt_execute($stmt)) {
				header('Location: ./?v=readers');
				exit;
			} else {
				$_SESSION['error_message'] = 'Error updating record: ' . mysqli_stmt_error($stmt);
				header('Location: ./?v=readers;f=alert');
				exit;
			}
// uzavreni prepared statementu
			mysqli_stmt_close($stmt);
		} else {
			$_SESSION['error_message'] = 'Error preparing the statement: ' . mysqli_error($connection) . '!';
			header('Location: ./?v=readers;f=alert');
			exit;
		}
	}
}
// ========== end READERS
// ========== begin LIMITACE
$sqlquery_select = "SELECT value FROM ConfigDU WHERE property = 'maxRows'";
$result_select = mysqli_query($connection, $sqlquery_select);
// kontrola, zda byl dotaz uspesny
if (!$result_select) {
	$_SESSION['error_message'] = 'Error retrieving maxRows: ' . mysqli_error($connection) . '!';
	header('Location: ./?v=more;f=alert');
	exit;
} else {
	if (mysqli_num_rows($result_select) > 0) {
		$row = mysqli_fetch_assoc($result_select);
		$max_rows_value = htmlspecialchars($row['value'], ENT_QUOTES, 'UTF-8');
	} else {
// vychozi hodnota, pokud neni nalezena v DB
		$max_rows_value = '1000';
	}
}
// ========== end LIMITACE
// ========== begin QUICK REBOOT
if ($key_view == $stat_view_array['r'] && $key_cond == $stat_cond_array['r']) {
// aktualizace tabulky running
	$sqlquery_update_running = "UPDATE running SET value = 1 WHERE property = 'restart' AND value = 0";
	$result_update_running = mysqli_query($connection, $sqlquery_update_running);
	if (!$result_update_running) {
		$_SESSION['error_message'] = 'Error updating running table: ' . mysqli_error($connection) . '!';
		header('Location: ./?v=more;f=alert');
		exit;
	} else {
// uspesna aktualizace, presmerovani zpet nebo jina akce
		$_SESSION['error_message'] = 'Unit restart has been set!';
		header('Location: ./?v=more;f=alert');
		exit;
	}
}
// ========== end QUICK REBOOT












// ========== begin TIME SYNC AND QUICK REBOOT
if ($key_view === $stat_view_array['r'] && $key_cond === $stat_cond_array['s']) {
	$sql_restart = "UPDATE running SET value = 1 WHERE property = 'restart' AND value = 0";
	if (!mysqli_query($connection, $sql_restart)) {
		$_SESSION['error_message'] = 'Error updating running table: ' . htmlspecialchars(mysqli_error($connection), ENT_QUOTES, 'UTF-8') . '!';
		header('Location: ./?v=more;f=alert');
		exit;
	}
	$client_time = (isset($_POST['client_time']) && $_POST['client_time'] !== '') ? $_POST['client_time'] : date('Y-m-d H:i:s');
	if (!$stmt_time = $connection->prepare("UPDATE running SET value = ? WHERE property = 'change_time'")) {
		$_SESSION['error_message'] = 'Error preparing TIME update: ' . htmlspecialchars($connection->error, ENT_QUOTES, 'UTF-8') . '!';
		header('Location: ./?v=more;f=alert');
		exit;
	}
	$stmt_time->bind_param('s', $client_time);
	if (!$stmt_time->execute()) {
		$_SESSION['error_message'] = 'Error updating TIME property: ' . htmlspecialchars($stmt_time->error, ENT_QUOTES, 'UTF-8') . '!';
		header('Location: ./?v=more;f=alert');
		exit;
	}
	if ($stmt_time->affected_rows === 0) {
		if ($stmt_ins = $connection->prepare("
			INSERT INTO running (property, value) VALUES ('change_time', ?)
			ON DUPLICATE KEY UPDATE value = VALUES(value)
		")) {
			$stmt_ins->bind_param('s', $client_time);
			$stmt_ins->execute();
			$stmt_ins->close();
		}
	}
	$stmt_time->close();
	$_SESSION['error_message'] = 'Unit time synced and restart has been set!';
	header('Location: ./?v=more;f=alert');
	exit;
}
// ========== end TIME SYNC AND QUICK REBOOT












// ========== begin STATUS
$status_value = '';
// ========== end STATUS
?><!DOCTYPE html>
<html lang="en">
<head>
	<title><?php
echo $title_view_array[$key_view];
?> &ndash; MeritAccess</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<meta name="meritaccess-gui" content="v=<?php
echo date('ymdHi', getlastmod());
?>">
	<meta name="color-scheme" content="light dark">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<link rel="stylesheet" type="text/css" href="<?php
echo fileVersioning('./css/global.css');
?>" media="screen">
	<link rel="stylesheet" type="text/css" href="<?php
echo fileVersioning('./css/style.css');
?>" media="screen">
	<script src="./lib/jquery-3.7.1.min.js"></script>
	<script src="./lib/jquery-ui.min.js"></script>
	<link rel="stylesheet" type="text/css" href="./lib/jquery-ui.min.css" media="screen">
	<script src="./lib/datatables.min.js"></script>
	<script src="./lib/dataTables.buttons.min.js"></script>
	<script src="./lib/jquery.fancybox.min.js"></script>
	<link rel="stylesheet" type="text/css" href="./lib/jquery.fancybox.min.css" media="screen">
	<script src="<?php
echo fileVersioning('./js/meritaccess-head.min.js');
?>"></script>
</head>
<body id="meritaccess">
<div id="server-time" data-time="<?php
echo $server_time;
?>"></div>
<div id="main">
<?php
if ($key_fact == $stat_fact_array['z']) {
?>
	<div id="error-info">
		<p><?php
// chybove hlasky
	echo $_SESSION['error_message'];
?></p>
	</div>
<?php
}
?>
	<div id="server-time-status">Time check in progress...</div>
	<div id="header">
		<h1>MeritAccess</h1>
		<p id="logo"><a href="./">MeritAccess<span class="local"><?php
// echo status
	echo $status_value;
?></span></a></p>
		<ul id="header-nav">
<?php
if ($key_view == $stat_view_array['c']) {
?>
			<li id="add-card"><a href="./?v=cards;s=past">Card</a></li>
<?php
}
if ($key_view == $stat_view_array['u']) {
?>
			<li id="add-user"><a href="./?v=users;s=past">User</a></li>
<?php
}
if ($key_view == $stat_view_array['t']) {
?>
			<li id="add-timezone"><a href="./?v=timezones;s=past">Timezone</a></li>
<?php
}
if ($key_view == $stat_view_array['o']) {
	if (isset($_SESSION['config_data_temp'])) {
?>
			<li id="save"><a href="./?v=config;s=past" onclick="return confirm('Are you sure you want to commit all changes?');">Confirm</a></li>
<?php
	}
}
?>
			<li id="info"><a href="#infobox" id="infobtn">Info</a></li>
<?php
// jiny pohled nez LOGIN
if ($key_view != $stat_view_array['l']) {
?>
			<li id="logout"><a href="./?v=drop">Logout</a></li>
<?php
}
?>
		</ul>
	</div>
	<div id="content">
<?php
// ========== begin pohled LOGIN
if ($key_view == $stat_view_array['l']) {
?>
		<h2>Login</h2>
		<p>Enter your login details, username and password.</p>
		<div id="form-container">
			<form action="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>" method="post" enctype="application/x-www-form-urlencoded" autocomplete="off">
				<label for="forusername">Username: </label>
				<input type="text" id="forusername" name="username" value="" maxlength="50" required="required" autocapitalize="off">
				<label for="forpassword">Password: </label>
				<input type="password" id="forpassword" name="password" value="" maxlength="50" required="required" autocapitalize="off">
				<div></div>
				<button type="submit" name="login">Login</button>
			</form>
		</div>
<?php
}
// ========== end pohled LOGIN
// ========== begin pohled HOME
if ($key_view == $stat_view_array['h']) {
?>
		<h2>Dashboard</h2>
		<p>Let's get started</p>
		<ul id="dashboard">
			<li id="access"><a href="./?v=access"><strong>Access</strong><br>Overview of access</a></li>
			<li id="cards"><a href="./?v=cards"><strong>Cards</strong><br>Card management</a></li>
			<li id="users"><a href="./?v=users"><strong>Users</strong><br>User management</a></li>
			<li id="more"><a href="./?v=more"><strong>More</strong><br>Other options</a></li>
		</ul>
<?php
}
// ========== end pohled HOME
// ========== begin pohled ACCESS
if ($key_view == $stat_view_array['a']) {
?>
		<h2>Access</h2>
		<p><a href="./?v=home">Dashboard</a> &rsaquo; Access</p>
<?php
// sql dotaz #1
	$sqlquery_1 = "SELECT Kdy, Karta, Ctecka, StavZpracovani, Pozn, Povoleni, Smazano, cardid FROM AccessDetails ORDER BY Kdy DESC LIMIT $max_rows_value";
// ochrana dotazu #1
	if ($result_1 = mysqli_query($connection, $sqlquery_1)) {
// kontrola, zda jsou dostupna data
		if (mysqli_num_rows($result_1) > 0) {
?>
		<div id="table-container">
			<table id="data-table">
				<thead>
					<tr>
						<th class="th-6">When</th>
						<th class="th-6">Card</th>
						<th class="th-6">Reader</th>
						<th class="th-6">State</th>
						<th class="th-6">Description</th>
						<th class="th-6">Access</th>
					</tr>
				</thead>
				<tbody>
<?php
// cyklus pro vypis dat #1
			while ($row_1 = mysqli_fetch_array($result_1)) {
				echo "\t\t\t\t\t";
				echo '<tr';
				if ($row_1['Smazano'] == 1) {
					echo ' class="tr-deleted"';
				}
				echo '>';
				echo '<td class="td-centered">' . $row_1['Kdy'] . '</td>';
				echo '<td class="td-centered">';
				if (is_null($row_1['cardid'])) {
					$row_1_edit = str_replace(' ', '-', $row_1['Karta']);
					echo '<a href="./?v=cards;s=past;i=' . $row_1_edit . '">' . $row_1['Karta'] . '</a>';
				} else {
					echo '<a href="./?v=cards;s=edit;i=' . $row_1['cardid'] . '">' . $row_1['Karta'] . '</a>';
				}
				echo '</td>';
				echo '<td class="td-centered">Reader [' . $row_1['Ctecka'] . ']</td>';
				echo '<td class="td-centered">' . $row_1['StavZpracovani'] . '</td>';
				echo '<td>';
				if (is_null($row_1['Pozn'])) {
					echo 'Unknown';
				} else {
					echo $row_1['Pozn'];
				}
				echo '</td>';
				echo '<td class="td-centered">';
				if (is_null($row_1['Povoleni'])) {
					echo 'Unknown';
				} else {
					echo $row_1['Povoleni'];
				}
				echo '</td>';
				echo '</tr>';
				echo "\n";
			}
?>
				</tbody>
			</table>
		</div>
<?php
// uvolneni pameti #1
			mysqli_free_result($result_1);
		} else {
			echo '<p>No data to display!</p>';
		}
	} else {
		echo '<p>Unable to process query! "' . $sqlquery_1 . '", Error! ' . mysqli_error($connection) . '</p>';
	}
}
// ========== end pohled ACCESS
// ========== begin pohled CARDS
// ========== begin pohled CARDS - list
if ($key_view == $stat_view_array['c'] && $key_cond != $stat_cond_array['p'] && $key_cond != $stat_cond_array['e'] && $key_cond != $stat_cond_array['d']) {
?>
		<h2>Cards</h2>
		<p><a href="./?v=home">Dashboard</a> &rsaquo; Cards</p>
<?php
// sql dotaz #2
	$sqlquery_2 = "SELECT * FROM Karty ORDER BY Karta ASC LIMIT $max_rows_value";
// ochrana dotazu #2
	if ($result_2 = mysqli_query($connection, $sqlquery_2)) {
// roztrhani vysledku sql dotazu #2
		if (mysqli_num_rows($result_2) > 0) {
?>
		<div id="table-container">
			<table id="data-table">
				<thead>
					<tr>
						<th class="th-7">Card</th>
						<th class="th-7">Reader</th>
						<th class="th-7">Timezone</th>
						<th class="th-7">Access</th>
						<th class="th-7">Description</th>
						<th class="th-7">Management</th>
						<th class="th-7">Permission</th>
					</tr>
				</thead>
				<tbody>
<?php
// cyklus pro vypis dat #2
			while ($row_2 = mysqli_fetch_array($result_2)) {
				echo '<tr';
				if ($row_2['Smazano'] == 1) {
					echo ' class="tr-deleted"';
				}
				echo '>';
				echo '<td class="td-centered nowrap"><a href="./?v=cards;s=edit;i=' . htmlspecialchars($row_2['cardid'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($row_2['Karta'], ENT_QUOTES, 'UTF-8') . '</a></td>';
				echo '<td class="td-centered">Reader [' . htmlspecialchars($row_2['Ctecka'], ENT_QUOTES, 'UTF-8') . ']</td>';
				echo '<td class="td-centered">';
				$zone_number_2 = htmlspecialchars($row_2['CasPlan'], ENT_QUOTES, 'UTF-8');
				$zone_name_2 = get_zone_name_by_number($connection, $zone_number_2);
				if ($zone_name_2) {
					echo 'Timezone [' . $zone_number_2 . '] &ndash; ' . htmlspecialchars($zone_name_2, ENT_QUOTES, 'UTF-8');
				} else {
					echo 'There is no zone!';
				}
				echo '</td>';
				echo '<td class="td-centered">' . htmlspecialchars($row_2['Povoleni'], ENT_QUOTES, 'UTF-8') . '</td>';
				echo '<td>' . htmlspecialchars($row_2['Pozn'], ENT_QUOTES, 'UTF-8') . '</td>';
				echo '<td class="td-centered"><a href="./?v=cards;s=edit;i=' . htmlspecialchars($row_2['cardid'], ENT_QUOTES, 'UTF-8') . '">Edit</a></td>';
				echo '<td class="td-centered"><a href="./?v=cards;s=delete;i=' . htmlspecialchars($row_2['cardid'], ENT_QUOTES, 'UTF-8') . '" onclick="return confirm(\'Card - ' . htmlspecialchars($row_2['Karta'], ENT_QUOTES, 'UTF-8') . ' - will be marked as blocked!\');">Block</a></td>';
				echo '</tr>';
			}
?>
				</tbody>
			</table>
		</div>
<?php
// uvolneni pameti #2
			mysqli_free_result($result_2);
		} else {
			echo '<p>No data to display!</p>';
		}
	} else {
		echo '<p>Unable to process query! "' . htmlspecialchars($sqlquery_2, ENT_QUOTES, 'UTF-8') . '", Error! ' . htmlspecialchars(mysqli_error($connection), ENT_QUOTES, 'UTF-8') . '</p>';
	}
}
// ========== end pohled CARDS - list
// ========== begin pohled CARDS - nova
if ($key_view == $stat_view_array['c'] && $key_cond == $stat_cond_array['p'] && $key_cond != $stat_cond_array['e'] && $key_cond != $stat_cond_array['d']) {
?>
		<h2>New card</h2>
		<p><a href="./?v=home">Dashboard</a> &rsaquo; <a href="./?v=cards" onclick="return confirm('Are you sure you want to leave without saving?');">Cards</a> &rsaquo; New card</p>
		<div id="form-container">
			<form action="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>" method="post" enctype="application/x-www-form-urlencoded" autocomplete="off">
				<input type="hidden" name="nove-smazano" value="0">
				<label for="fornovakarta">Card <span class="required">*</span></label>
				<input type="text" id="fornovakarta" name="nova-karta" value="<?php echo str_replace('-', ' ', htmlspecialchars($key_info ?? '', ENT_QUOTES, 'UTF-8')); ?>" maxlength="50" required="required">
				<label for="fornovactecka">Reader <span class="required">*</span></label>
				<select id="fornovactecka" name="nova-ctecka" required="required">
<?php
	$sqlquery_reader = "SELECT id FROM Readers ORDER BY id ASC";
	$result_reader = mysqli_query($connection, $sqlquery_reader);
	while ($row_reader = mysqli_fetch_assoc($result_reader)) {
		$reader_id = htmlspecialchars($row_reader['id'], ENT_QUOTES, 'UTF-8');
		echo '<option value="' . $reader_id . '">Reader [' . $reader_id . ']</option>';
	}
?>
				</select>
				<label for="fornovycasplan">Timezone <span class="required">*</span></label>
				<select id="fornovycasplan" name="novy-cas-plan" required="required">
					<option value="0" selected="selected">No timezone [0]</option>
<?php
	$sqlquery_timezone = "SELECT Cislo, Nazev FROM CasovePlany ORDER BY Cislo ASC";
	$result_timezone = mysqli_query($connection, $sqlquery_timezone);
	while ($row_timezone = mysqli_fetch_assoc($result_timezone)) {
		$timezone_value = htmlspecialchars($row_timezone['Cislo'], ENT_QUOTES, 'UTF-8');
		$timezone_name = htmlspecialchars($row_timezone['Nazev'], ENT_QUOTES, 'UTF-8');
		echo '<option value="' . $timezone_value . '">Timezone [' . $timezone_value . '] &ndash; ' . $timezone_name . '</option>';
	}
?>
				</select>
				<label for="fornovepovoleni">Access <span class="required">*</span></label>
				<input type="text" id="fornovepovoleni" name="nove-povoleni" value="1" maxlength="50" placeholder="1 &ndash; default" required="required">
				<label for="fornovepozn">Description</label>
				<input type="text" id="fornovepozn" name="nove-pozn" value="" maxlength="50" required="required">
				<div></div>
				<button type="submit" name="pridat-karta">Create</button>
			</form>
		</div>
		<p><span class="required">* Required</span></p>
		<p><a href="./?v=cards" onclick="return confirm('Are you sure you want to leave without saving?');">&lsaquo; Back on Cards</a></p>
<?php
}
// ========== end pohled CARDS - nova
// ========== begin pohled CARDS - editace
if ($key_view == $stat_view_array['c'] && $key_cond != $stat_cond_array['p'] && $key_cond == $stat_cond_array['e'] && $key_cond != $stat_cond_array['d']) {
?>
		<h2>Edit card</h2>
		<p><a href="./?v=home">Dashboard</a> &rsaquo; <a href="./?v=cards" onclick="return confirm('Are you sure you want to leave without saving?');">Cards</a> &rsaquo; Edit card</p>
		<div id="form-container">
			<form action="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>" method="post" enctype="application/x-www-form-urlencoded" autocomplete="off">
				<input type="hidden" name="cardid" value="<?php echo htmlspecialchars($row_select['cardid'], ENT_QUOTES, 'UTF-8'); ?>">
				<label for="foreditkarta">Card (read only)</label>
				<input type="text" id="foreditkarta" name="edit-karta" value="<?php echo htmlspecialchars($row_select['Karta'], ENT_QUOTES, 'UTF-8'); ?>" readonly="readonly">
				<label for="foreditctecka">Reader <span class="required">*</span></label>
				<select id="foreditctecka" name="edit-ctecka" required="required">
<?php
	$sqlquery_reader = "SELECT id FROM Readers ORDER BY id ASC";
	$result_reader = mysqli_query($connection, $sqlquery_reader);
	while ($row_reader = mysqli_fetch_assoc($result_reader)) {
		$reader_value = htmlspecialchars($row_reader['id'], ENT_QUOTES, 'UTF-8');
		echo '<option value="' . $reader_value . '"';
		if ($row_select['Ctecka'] == $reader_value) {
			echo ' selected="selected"';
		}
		echo '>Reader [' . $reader_value . ']</option>';
	}
?>
				</select>
				<label for="foreditcasplan">Timezone <span class="required">*</span></label>
				<select id="foreditcasplan" name="edit-cas-plan" required="required">
					<option value="0">No timezone [0]</option>
<?php
	$sqlquery_timezone = "SELECT Cislo, Nazev FROM CasovePlany ORDER BY Cislo ASC";
	$result_timezone = mysqli_query($connection, $sqlquery_timezone);
	while ($row_timezone = mysqli_fetch_assoc($result_timezone)) {
		$timezone_value = htmlspecialchars($row_timezone['Cislo'], ENT_QUOTES, 'UTF-8');
		$timezone_name = htmlspecialchars($row_timezone['Nazev'], ENT_QUOTES, 'UTF-8');
		echo '<option value="' . $timezone_value . '"';
		if ($row_select['CasPlan'] == $timezone_value) {
			echo ' selected="selected"';
		}
		echo '>Timezone [' . $timezone_value . '] &ndash; ' . $timezone_name . '</option>';
	}
?>
				</select>
				<label for="foreditpovoleni">Access <span class="required">*</span></label>
				<input type="text" id="foreditpovoleni" name="edit-povoleni" value="<?php echo htmlspecialchars($row_select['Povoleni'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="50" required="required">
				<label for="foreditsmazano">Rights <span class="required">*</span></label>
				<select id="foreditsmazano" name="edit-smazano" required="required">
					<option value="">&ndash;</option>
<?php
	$row_selected = htmlspecialchars($row_select['Smazano'], ENT_QUOTES, 'UTF-8');
	if ($row_selected == '0') {
		echo '<option value="0" selected="selected">Enabled</option>';
		echo '<option value="1">Block</option>';
	} elseif ($row_selected == '1') {
		echo '<option value="0">Enable</option>';
		echo '<option value="1" selected="selected">Blocked</option>';
	} else {
		echo '<option value="0">Enable</option>';
		echo '<option value="1">Block</option>';
	}
?>
				</select>
				<label for="foreditpozn">Description</label>
				<input type="text" id="foreditpozn" name="edit-pozn" value="<?php echo htmlspecialchars($row_select['Pozn'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="50">
				<div></div>
				<button type="submit" name="edit-zaznam" onclick="return confirm('Are you sure you want to edit this listing?');">Commit changes</button>
			</form>
		</div>
		<p><span class="required">* Required</span></p>
		<p><a href="./?v=cards" onclick="return confirm('Are you sure you want to leave without saving?');">&lsaquo; Back on Cards</a></p>
<?php
}
// ========== end pohled CARDS - editace
// ========== end pohled CARDS
// ========== begin pohled USERS
// ========== begin pohled USERS - list
if ($key_view == $stat_view_array['u'] && $key_cond != $stat_cond_array['p'] && $key_cond != $stat_cond_array['e'] && $key_cond != $stat_cond_array['d']) {
?>
		<h2>Users</h2>
		<p><a href="./?v=home">Dashboard</a> &rsaquo; Users</p>
<?php
// sql dotaz #4
	$sqlquery_4 = "SELECT * FROM users ORDER BY LogonName ASC";
// ochrana dotazu #4
	if ($result_4 = mysqli_query($connection, $sqlquery_4)) {
// kontrola, zda jsou dostupna data
		if (mysqli_num_rows($result_4) > 0) {
?>
		<div id="table-container">
			<table id="data-table">
				<thead>
					<tr>
						<th class="th-3">User</th>
						<th class="th-3">Management</th>
						<th class="th-3">Rights</th>
					</tr>
				</thead>
				<tbody>
<?php
// cyklus pro vypis dat #4
			while ($row_4 = mysqli_fetch_array($result_4)) {
				echo "\t\t\t\t\t";
				echo '<tr';
				if ($row_4['rights'] == 0) {
					echo ' class="tr-deleted"';
				}
				echo '>';
				echo '<td>' . $row_4['LogonName'] . '</td>';
				echo '<td class="td-centered"><a href="./?v=users;s=edit;i=' . $row_4['id'] . '">Edit</a></td>';
				echo '<td class="td-centered"><a href="./?v=users;s=delete;i=' . $row_4['id'] . '" onclick="return confirm(\'User - ' . $row_4['LogonName'] . ' - will be marked as blocked!\');">Block</a></td>';
				echo '</tr>';
				echo "\n";
			}
?>
				</tbody>
			</table>
		</div>
<?php
// uvolneni pameti #4
			mysqli_free_result($result_4);
		} else {
			echo '<p>No data to display!</p>';
		}
	} else {
		echo '<p>Unable to process query! "' . $sqlquery_4 . '", Error! ' . mysqli_error($connection) . '</p>';
	}
}
// ========== end pohled USERS - list
// ========== begin pohled USERS - novy
if ($key_view == $stat_view_array['u'] && $key_cond == $stat_cond_array['p'] && $key_cond != $stat_cond_array['e'] && $key_cond != $stat_cond_array['d']) {
?>
		<h2>New user</h2>
		<p><a href="./?v=home">Dashboard</a> &rsaquo; <a href="./?v=users" onclick="return confirm('Are you sure you want to leave without saving?');">Users</a> &rsaquo; New user</p>
		<div id="form-container">
			<form action="<?php
echo $url;
?>" method="post" enctype="application/x-www-form-urlencoded" autocomplete="off">
				<label for="fornovyuser">Username <span class="required">*</span></label>
				<input type="text" id="fornovyuser" name="novy-user" value="" maxlength="50" required="required">
				<label for="fornovymd5">Password <span class="required">*</span></label>
				<input type="password" id="fornovymd5" name="novy-md5" value="" maxlength="50" required="required" onkeyup="checkPasswordStrength();">
				<div></div>
				<div id="password-strength-status"></div>
				<input type="hidden" name="nova-prava" value="255">
				<div></div>
				<button type="submit" name="novy-uzivatel">Create</button>
			</form>
		</div>
		<p><span class="required">* Required</span></p>
		<p><a href="./?v=users" onclick="return confirm('Are you sure you want to leave without saving?');">&lsaquo; Back on Users</a></p>
<?php
}
// ========== end pohled USERS - novy
// ========== begin pohled USERS - editace
if ($key_view == $stat_view_array['u'] && $key_cond != $stat_cond_array['p'] && $key_cond == $stat_cond_array['e'] && $key_cond != $stat_cond_array['d']) {
?>
		<h2>Edit user</h2>
		<p><a href="./?v=home">Dashboard</a> &rsaquo; <a href="./?v=users" onclick="return confirm('Are you sure you want to leave without saving?');">Users</a> &rsaquo; Edit user</p>
		<div id="form-container">
			<form action="<?php
echo $url;
?>" method="post" enctype="application/x-www-form-urlencoded" autocomplete="off">
				<input type="hidden" name="id" value="<?php
echo $row_select['id'];
?>">
				<label for="foredituser">Username (read only)</label>
				<input type="text" id="foredituser" name="edit-user" value="<?php
echo $row_select['LogonName'];
?>" readonly="readonly">
				<label for="foreditmd5">Password (new only) <span class="required">*</span></label>
				<input type="password" id="foreditmd5" name="edit-md5" value="" maxlength="50" required="required" onkeyup="checkPasswordStrength();">
				<div></div>
				<div id="password-strength-status"></div>
				<label for="foreditprava">Rights</label>
				<select id="foreditprava" name="edit-prava" required="required">
					<option value="">&ndash;</option>
<?php
	$row_selected = $row_select['rights'];
	if ($row_selected == '255') {
?>
					<option value="255" selected="selected">Enabled</option>
					<option value="0">Block</option>
<?php
	} elseif ($row_selected == '0') {
?>
					<option value="255">Enable</option>
					<option value="0" selected="selected">Blocked</option>
<?php
	} else {
?>
					<option value="255">Enable</option>
					<option value="0">Block</option>
<?php
	}
?>
				</select>
				<div></div>
				<button type="submit" name="edit-uzivatel">Commit changes</button>
			</form>
		</div>
		<p><span class="required">* Required</span></p>
		<p><a href="./?v=users" onclick="return confirm('Are you sure you want to leave without saving?');">&lsaquo; Back on Users</a></p>
<?php
}
// ========== end pohled USERS - editace
// ========== end pohled USERS
// ========== begin pohled MORE
if ($key_view == $stat_view_array['m']) {
?>
		<h2>More</h2>
		<p><a href="./?v=home">Dashboard</a> &rsaquo; More</p>
		<ul id="more">
			<li><strong id="more-title-first">Administration</strong></li>
			<li id="config"><a href="./?v=config">Configuration</a></li>
			<li id="running"><a href="./?v=running">Running</a></li>
			<li id="timezones"><a href="./?v=timezones">Timezones</a></li>
			<li id="readers"><a href="./?v=readers">Readers</a></li>
			<li><strong>New items</strong></li>
			<li id="add-card"><a href="./?v=cards;s=past">New card</a></li>
			<li id="add-user"><a href="./?v=users;s=past">New user</a></li>
			<li id="add-timezone"><a href="./?v=timezones;s=past">New timezone</a></li>
			<li><strong>Quick action</strong></li>
			<li id="reload"><a href="./?v=running;s=reboot" onclick="return confirm('Are you sure you want to reboot the unit?');">Restart the unit</a></li>
			<li id="resync"><a href="./?v=running;s=resync">Sync time and restart</a></li>
			<li><strong>Information</strong></li>
			<li id="logs"><a href="./?v=logs">Logs</a></li>
		</ul>
<?php
}
// ========== end pohled MORE
// ========== begin pohled CONFIGDU
// ========== begin pohled CONFIGDU - list
if ($key_view == $stat_view_array['o'] && $key_cond != $stat_cond_array['e'] && $key_cond != $stat_cond_array['p']) {
?>
		<h2>Configuration</h2>
		<p><a href="./?v=home">Dashboard</a> &rsaquo; <a href="./?v=more">More</a> &rsaquo; Configuration</p>
		<div id="table-container">
<?php
	$config_data = array();
	$sqlquery_5 = "SELECT * FROM ConfigByGroups ORDER BY groupname ASC";
	if ($result_5 = mysqli_query($connection, $sqlquery_5)) {
		if (mysqli_num_rows($result_5) > 0) {
			while ($row_5 = mysqli_fetch_array($result_5)) {
				$config_data[] = $row_5;
			}
			$_SESSION['config_data'] = $config_data;
			mysqli_free_result($result_5);
		} else {
			echo '<p>No data to display!</p>';
		}
	} else {
		echo '<p>Unable to process query! "' . htmlspecialchars($sqlquery_5, ENT_QUOTES, 'UTF-8') . '", Error! ' . htmlspecialchars(mysqli_error($connection), ENT_QUOTES, 'UTF-8') . '</p>';
	}
	$config_data_view = isset($_SESSION['config_data_temp']) ? $_SESSION['config_data_temp'] : $_SESSION['config_data'];
	if (!empty($config_data_view)) {
// rozdel data podle groupname
		$grouped_data = [];
		foreach ($config_data_view as $row_5) {
			$grouped_data[$row_5['groupname']][] = $row_5;
		}
// vypis tabulky pro kazdou skupinu
		foreach ($grouped_data as $groupname => $group_data) {
			echo '<p class="data-table-cap"><span class="cap-plus"></span>' . htmlspecialchars($groupname, ENT_QUOTES, 'UTF-8') . '</p>';
			echo "\n";
			echo '<div class="table-container-scroll">';
			echo "\n";
			echo '<table class="data-table-coll">';
			echo '<thead>';
			echo '<tr>';
			echo '<th class="th-3">Property</th>';
			echo '<th class="th-3">Value</th>';
			echo '<th class="th-3">Management</th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
			echo "\n";
// vypis radky tabulky pro danou skupinu
			foreach ($group_data as $row_5) {
				echo '<tr>';
				echo '<td class="td-upper">' . htmlspecialchars($row_5['property'], ENT_QUOTES, 'UTF-8') . '</td>';
				echo '<td>' . htmlspecialchars($row_5['value'], ENT_QUOTES, 'UTF-8') . '</td>';
				echo '<td class="td-centered"><a href="./?v=config;s=edit;i=' . htmlspecialchars($row_5['id'], ENT_QUOTES, 'UTF-8') . '">Edit</a></td>';
				echo '</tr>';
				echo "\n";
			}
			echo '</tbody>';
			echo "\n";
			echo '</table>';
			echo "\n";
			echo '</div>';
		}
	} else {
		echo '<p>No data in SESSION!</p>';
	}
?>
		<hr>
		</div>
<?php
}
// ========== end pohled CONFIGDU - list
// ========== begin pohled CONFIGDU - editace
if ($key_view == $stat_view_array['o'] && $key_cond == $stat_cond_array['e']) {
?>
		<h2>Edit configuration</h2>
		<p><a href="./?v=home">Dashboard</a> &rsaquo; <a href="./?v=more">More</a> &rsaquo; <a href="./?v=config" onclick="return confirm('Are you sure you want to leave without saving?');">Configuration</a> &rsaquo; Edit configuration</p>
		<div id="form-container">
			<form action="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>" method="post" enctype="application/x-www-form-urlencoded" autocomplete="off">
<?php
// pokud pole config_data existuje v session
	if (isset($_SESSION['config_data'])) {
// projit vsechny polozky pole
		foreach ($_SESSION['config_data'] as $row_5) {
// pokud se ID polozky shoduje s ID, ktere chcete ponechat
			if ($row_5['id'] == $key_info) {
?>
				<input type="hidden" name="id" value="<?php echo htmlspecialchars($row_5['id'], ENT_QUOTES, 'UTF-8'); ?>">
				<label for="foreditproperty">Property</label>
				<input type="text" id="foreditproperty" name="edit-property" value="<?php echo htmlspecialchars($row_5['property'], ENT_QUOTES, 'UTF-8'); ?>" readonly="readonly">
				<label for="foreditvalue">Value <span class="required">*</span></label>
				<input type="text" id="foreditvalue" name="edit-value" value="<?php echo htmlspecialchars($row_5['value'], ENT_QUOTES, 'UTF-8'); ?>" title="Example: <?php echo htmlspecialchars($row_5['sample'], ENT_QUOTES, 'UTF-8'); ?>" required="required">
				<input type="hidden" name="configdu-valid" value="<?php echo htmlspecialchars($row_5['regex'], ENT_QUOTES, 'UTF-8'); ?>">
				<div></div>
				<button type="submit" name="edit-config" onclick="return confirm('Are you sure you want to edit this listing?');">Commit changes</button>
<?php
			}
		}
	} else {
		echo '<p>No data in SESSION!</p>';
	}
?>
			</form>
		</div>
		<p><span class="required">* Required</span></p>
		<p><a href="./?v=config" onclick="return confirm('Are you sure you want to leave without saving?');">&lsaquo; Back on Configuration</a></p>
<?php
}
// ========== end pohled CONFIGDU - editace
// ========== end pohled CONFIGDU
// ========== begin pohled RUNNING
if ($key_view == $stat_view_array['r']) {
?>
		<h2>Running</h2>
		<p><a href="./?v=home">Dashboard</a> &rsaquo; <a href="./?v=more">More</a> &rsaquo; Running</p>
<?php
// sql dotaz #3
	$sqlquery_3 = "SELECT * FROM running ORDER BY lastchange DESC";
// ochrana dotazu #3
	if ($result_3 = mysqli_query($connection, $sqlquery_3)) {
// kontrola, zda jsou dostupna data
		if (mysqli_num_rows($result_3) > 0) {
?>
		<div id="table-container">
			<table id="data-table">
				<thead>
					<tr>
						<th class="th-3">Property</th>
						<th class="th-3">Value</th>
						<th class="th-3">Last change</th>
					</tr>
				</thead>
				<tbody>
<?php
// cyklus pro vypis dat #3
			while ($row_3 = mysqli_fetch_array($result_3)) {
				echo '<tr>';
				echo '<td class="td-upper">' . htmlspecialchars($row_3['property'], ENT_QUOTES, 'UTF-8') . '</td>';
				echo '<td>' . htmlspecialchars($row_3['value'], ENT_QUOTES, 'UTF-8') . '</td>';
				echo '<td class="td-centered">' . htmlspecialchars($row_3['lastchange'], ENT_QUOTES, 'UTF-8') . '</td>';
				echo '</tr>' . "\n";
			}
?>
					<tr>
						<td class="td-upper">Date, time</td>
						<td><span id="datetime2"></span></td>
						<td class="td-centered"><?php
echo date('Y-m-d H:i:s');
?></td>
					</tr>
				</tbody>
			</table>
		</div>
<?php
// uvolneni pameti #3
			mysqli_free_result($result_3);
		} else {
			echo '<p>No data to display!</p>';
		}
	} else {
		echo '<p>Unable to process query! "' . htmlspecialchars($sqlquery_3, ENT_QUOTES, 'UTF-8') . '", Error! ' . htmlspecialchars(mysqli_error($connection), ENT_QUOTES, 'UTF-8') . '</p>';
	}
}
// ========== end pohled RUNNING
// ========== begin pohled TIMEZONES
// ========== begin pohled TIMEZONES - list
if ($key_view == $stat_view_array['t'] && $key_cond != $stat_cond_array['p'] && $key_cond != $stat_cond_array['e'] && $key_cond != $stat_cond_array['d']) {
?>
		<h2>Timezones</h2>
		<p><a href="./?v=home">Dashboard</a> &rsaquo; <a href="./?v=more">More</a> &rsaquo; Timezones</p>
<?php
// sql dotaz #7
	$sqlquery_7 = "SELECT * FROM CasovePlany ORDER BY Cislo ASC LIMIT ?";
	if ($stmt_7 = mysqli_prepare($connection, $sqlquery_7)) {
		mysqli_stmt_bind_param($stmt_7, "i", $max_rows_value);
		mysqli_stmt_execute($stmt_7);
		$result_7 = mysqli_stmt_get_result($stmt_7);
		if (mysqli_num_rows($result_7) > 0) {
?>
		<div id="table-container">
			<table id="data-table">
				<thead>
					<tr>
						<th class="th-5">Number</th>
						<th class="th-5">Name</th>
						<th class="th-5">Description</th>
						<th class="th-5">State</th>
						<th class="th-5">Edit</th>
					</tr>
				</thead>
				<tbody>
<?php
			$row_status_7 = ['0' => 'No system plan', '1' => 'Silent open', '2' => 'Pulse', '3' => 'Reverse'];
			while ($row_7 = mysqli_fetch_array($result_7)) {
				echo '<tr' . ($row_7['Smazano'] == 1 ? ' class="tr-deleted"' : '') . '>';
				echo '<td><a href="./?v=timezones;s=edit;i=' . htmlspecialchars($row_7['Id_CasovyPlan'], ENT_QUOTES, 'UTF-8') . '">Timezone [' . htmlspecialchars($row_7['Cislo'], ENT_QUOTES, 'UTF-8') . ']</a></td>';
				echo '<td>' . htmlspecialchars($row_7['Nazev'], ENT_QUOTES, 'UTF-8') . '</td>';
				echo '<td>' . htmlspecialchars($row_7['Popis'], ENT_QUOTES, 'UTF-8') . '</td>';
				echo '<td class="td-centered">' . htmlspecialchars($row_status_7[$row_7['RezimOtevirani']], ENT_QUOTES, 'UTF-8') . '</td>';
				echo '<td><a href="./?v=timezones;s=edit;i=' . htmlspecialchars($row_7['Id_CasovyPlan'], ENT_QUOTES, 'UTF-8') . '">Edit</a></td>';
				echo '</tr>';
				echo "\n";
			}
?>
				</tbody>
			</table>
		</div>
<?php
			mysqli_free_result($result_7);
		} else {
			echo '<p>No data to display!</p>';
		}
	} else {
		echo '<p>Unable to process query! "' . htmlspecialchars($sqlquery_7, ENT_QUOTES, 'UTF-8') . '", Error! ' . htmlspecialchars(mysqli_error($connection), ENT_QUOTES, 'UTF-8') . '</p>';
	}
}
// ========== end pohled TIMEZONES - list
// ========== begin pohled TIMEZONES - nova
if ($key_view == $stat_view_array['t'] && $key_cond == $stat_cond_array['p'] && $key_cond != $stat_cond_array['e'] && $key_cond != $stat_cond_array['d']) {
?>
		<h2>Timezones</h2>
		<p><a href="./?v=home">Dashboard</a> &rsaquo; <a href="./?v=more">More</a> &rsaquo; <a href="./?v=timezones" onclick="return confirm('Are you sure you want to leave without saving?');">Timezones</a> &rsaquo; New timezone</p>
		<div id="form-container">
			<form action="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>" method="post" enctype="application/x-www-form-urlencoded" autocomplete="off">
				<label for="fornovynumber">Number <span class="required">*</span></label>
				<select id="nove-cislo" name="nove-cislo" required="required">
<?php
	$free_numbers = get_free_zone_numbers($connection);
	foreach ($free_numbers as $number) {
		echo '<option value="' . htmlspecialchars($number, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($number, ENT_QUOTES, 'UTF-8') . '</option>';
	}
?>
				</select>
				<label for="fornovyname">Name <span class="required">*</span></label>
				<input type="text" id="fornovyname" name="novy-nazev" maxlength="50" required="required">
				<label for="fornovydesc">Description <span class="required">*</span></label>
				<input type="text" id="fornovydesc" name="novy-popis" maxlength="50" required="required">
				<label for="fornovystate">State <span class="required">*</span></label>
				<select id="fornovystate" name="novy-stav" required="required">
					<option value="0">No system plan</option>
					<option value="1">Silent open</option>
					<option value="2" selected="selected">Pulse</option>
					<option value="3">Reverse</option>
				</select>
				<div></div>
				<div class="matrix-4">
					<label for="tempstart1">From</label>
					<label for="tempend1">To</label>
					<label for="tempstart2">From</label>
					<label for="tempend2">To</label>
				</div>
				<label for="">Template</label>
				<div class="matrix-4">
					<input type="text" id="tempstart1" name="tempstart1n" maxlength="50" placeholder="HH:MM:SS – required" class="time-hhmmss">
					<input type="text" id="tempend1" name="tempend1n" maxlength="50" placeholder="HH:MM:SS – required" class="time-hhmmss">
					<input type="text" id="tempstart2" name="tempstart2n" maxlength="50" placeholder="HH:MM:SS – optional" class="time-hhmmss">
					<input type="text" id="tempend2" name="tempend2n" maxlength="50" placeholder="HH:MM:SS – optional" class="time-hhmmss">
				</div>
				<div></div>
				<button type="button" id="copy-data">Copy template</button>
<?php
	$timezones_days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday', 'Holiday'];
	$timezones_days_short = ['mo', 'tu', 'we', 'th', 'fr', 'sa', 'su', 'ho'];
	for ($i = 0; $i < 8; $i++) {
		$timezones_day_week = htmlspecialchars($timezones_days[$i], ENT_QUOTES, 'UTF-8');
		$timezones_input_var = htmlspecialchars($timezones_days_short[$i], ENT_QUOTES, 'UTF-8');
		echo '<div></div>';
		echo '<div class="matrix-4">';
		echo '<label for="' . $timezones_input_var . 'start1">From <span class="required">*</span></label>';
		echo '<label for="' . $timezones_input_var . 'end1">To <span class="required">*</span></label>';
		echo '<label for="' . $timezones_input_var . 'start2">From</label>';
		echo '<label for="' . $timezones_input_var . 'end2">To</label>';
		echo '</div>';
		echo '<label for="">' . $timezones_day_week . '</label>';
		echo '<div class="matrix-4">';
		echo '<input type="text" id="' . $timezones_input_var . 'start1" name="' . $timezones_input_var . 'start1v" maxlength="50" placeholder="HH:MM:SS – required" required="required" class="time-hhmmss">';
		echo '<input type="text" id="' . $timezones_input_var . 'end1" name="' . $timezones_input_var . 'end1v" maxlength="50" placeholder="HH:MM:SS – required" required="required" class="time-hhmmss">';
		echo '<input type="text" id="' . $timezones_input_var . 'start2" name="' . $timezones_input_var . 'start2v" maxlength="50" placeholder="HH:MM:SS – optional" class="time-hhmmss">';
		echo '<input type="text" id="' . $timezones_input_var . 'end2" name="' . $timezones_input_var . 'end2v" maxlength="50" placeholder="HH:MM:SS – optional" class="time-hhmmss">';
		echo '</div>';
	}
?>
				<div></div>
				<button type="submit" name="nova-zona">Create</button>
			</form>
		</div>
		<p><span class="required">* Required</span></p>
		<p><a href="./?v=timezones" onclick="return confirm('Are you sure you want to leave without saving?');">&lsaquo; Back on Timezones</a></p>
<?php
}
// ========== end pohled TIMEZONES - nova
// ========== begin pohled TIMEZONES - editace
if ($key_view == $stat_view_array['t'] && $key_cond != $stat_cond_array['p'] && $key_cond == $stat_cond_array['e'] && $key_cond != $stat_cond_array['d']) {
?>
		<h2>Timezones</h2>
		<p><a href="./?v=home">Dashboard</a> &rsaquo; <a href="./?v=more">More</a> &rsaquo; <a href="./?v=timezones" onclick="return confirm('Are you sure you want to leave without saving?');">Timezones</a> &rsaquo; Edit timezone</p>
		<div id="form-container">
			<form action="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>" method="post" enctype="application/x-www-form-urlencoded" autocomplete="off">
				<input type="hidden" name="id" value="<?php echo htmlspecialchars($row_select['Id_CasovyPlan'], ENT_QUOTES, 'UTF-8'); ?>">
				<label for="foreditnumber">Number</label>
				<select id="edit-cislo" name="edit-cislo" required="required">
<?php
	$free_numbers = get_free_zone_numbers($connection);
	$current_number = htmlspecialchars($row_select['Cislo'], ENT_QUOTES, 'UTF-8');
	echo '<option value="' . $current_number . '" selected="selected">' . $current_number . '</option>';
	foreach ($free_numbers as $number) {
		echo '<option value="' . htmlspecialchars($number, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($number, ENT_QUOTES, 'UTF-8') . '</option>';
	}
?>
				</select>
				<label for="foreditname">Name <span class="required">*</span></label>
				<input type="text" id="foreditname" name="edit-nazev" value="<?php echo htmlspecialchars($row_select['Nazev'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="50" required="required">
				<label for="foreditdesc">Description <span class="required">*</span></label>
				<input type="text" id="foreditdesc" name="edit-popis" value="<?php echo htmlspecialchars($row_select['Popis'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="50" required="required">
				<label for="foreditstate">State <span class="required">*</span></label>
				<select id="foreditstate" name="edit-stav" required="required">
<?php
	$row_selected = htmlspecialchars($row_select['RezimOtevirani'], ENT_QUOTES, 'UTF-8');
	$state_options = ['0' => 'No system plan', '1' => 'Silent open', '2' => 'Pulse', '3' => 'Reverse'];
	foreach ($state_options as $value => $label) {
		echo '<option value="' . $value . '"' . ($row_selected == $value ? ' selected="selected"' : '') . '>' . $label . '</option>';
	}
?>
				</select>
				<div></div>
				<div class="matrix-4">
					<label for="tempstart1">From</label>
					<label for="tempend1">To</label>
					<label for="tempstart2">From</label>
					<label for="tempend2">To</label>
				</div>
				<label for="">Template</label>
				<div class="matrix-4">
					<input type="text" id="tempstart1" name="tempstart1n" value="00:00:00" maxlength="50" placeholder="HH:MM:SS – required" class="time-hhmmss">
					<input type="text" id="tempend1" name="tempend1n" value="00:00:00" maxlength="50" placeholder="HH:MM:SS – required" class="time-hhmmss">
					<input type="text" id="tempstart2" name="tempstart2n" value="00:00:00" maxlength="50" placeholder="HH:MM:SS – optional" class="time-hhmmss">
					<input type="text" id="tempend2" name="tempend2n" value="00:00:00" maxlength="50" placeholder="HH:MM:SS – optional" class="time-hhmmss">
				</div>
				<div></div>
				<button type="button" id="copy-data">Copy template</button>
<?php
	$timezones_days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday', 'Holiday'];
	$timezones_days_short = ['mo', 'tu', 'we', 'th', 'fr', 'sa', 'su', 'ho'];
	$timezones_days_sql = ['Po', 'Ut', 'St', 'Ct', 'Pa', 'So', 'Ne', 'Svatky'];
	for ($i = 0; $i < 8; $i++) {
		$timezones_day_week = htmlspecialchars($timezones_days[$i], ENT_QUOTES, 'UTF-8');
		$timezones_input_var = htmlspecialchars($timezones_days_short[$i], ENT_QUOTES, 'UTF-8');
		$timezones_day_sql = htmlspecialchars($timezones_days_sql[$i], ENT_QUOTES, 'UTF-8');
		$start1 = isset($row_select[$timezones_day_sql . '_PrvniZacatek']) ? htmlspecialchars($row_select[$timezones_day_sql . '_PrvniZacatek'], ENT_QUOTES, 'UTF-8') : '';
		$end1 = isset($row_select[$timezones_day_sql . '_PrvniKonec']) ? htmlspecialchars($row_select[$timezones_day_sql . '_PrvniKonec'], ENT_QUOTES, 'UTF-8') : '';
		$start2 = isset($row_select[$timezones_day_sql . '_DruhyZacatek']) ? htmlspecialchars($row_select[$timezones_day_sql . '_DruhyZacatek'], ENT_QUOTES, 'UTF-8') : '';
		$end2 = isset($row_select[$timezones_day_sql . '_DruhyKonec']) ? htmlspecialchars($row_select[$timezones_day_sql . '_DruhyKonec'], ENT_QUOTES, 'UTF-8') : '';
		echo '<div></div>';
		echo '<div class="matrix-4">';
		echo '<label for="' . $timezones_input_var . 'start1">From <span class="required">*</span></label>';
		echo '<label for="' . $timezones_input_var . 'end1">To <span class="required">*</span></label>';
		echo '<label for="' . $timezones_input_var . 'start2">From</label>';
		echo '<label for="' . $timezones_input_var . 'end2">To</label>';
		echo '</div>';
		echo '<label for="">' . $timezones_day_week . '</label>';
		echo '<div class="matrix-4">';
		echo '<input type="text" id="' . $timezones_input_var . 'start1" name="' . $timezones_input_var . 'start1v" value="' . $start1 . '" maxlength="50" placeholder="HH:MM:SS – required" required="required" class="time-hhmmss">';
		echo '<input type="text" id="' . $timezones_input_var . 'end1" name="' . $timezones_input_var . 'end1v" value="' . $end1 . '" maxlength="50" placeholder="HH:MM:SS – required" required="required" class="time-hhmmss">';
		echo '<input type="text" id="' . $timezones_input_var . 'start2" name="' . $timezones_input_var . 'start2v" value="' . $start2 . '" maxlength="50" placeholder="HH:MM:SS – optional" class="time-hhmmss">';
		echo '<input type="text" id="' . $timezones_input_var . 'end2" name="' . $timezones_input_var . 'end2v" value="' . $end2 . '" maxlength="50" placeholder="HH:MM:SS – optional" class="time-hhmmss">';
		echo '</div>';
	}
?>
				<div></div>
				<button type="submit" name="edit-zona" onclick="return confirm('Are you sure you want to edit this listing?');">Commit changes</button>
			</form>
		</div>
		<p><span class="required">* Required</span></p>
		<p><a href="./?v=timezones" onclick="return confirm('Are you sure you want to leave without saving?');">&lsaquo; Back on Timezones</a></p>
<?php
}
// ========== end pohled TIMEZONES - editace
// ========== end pohled TIMEZONES
// ========== begin pohled READERS
// ========== begin pohled READERS - list
if ($key_view == $stat_view_array['g'] && $key_cond != $stat_cond_array['e']) {
?>
		<h2>Readers</h2>
		<p><a href="./?v=home">Dashboard</a> &rsaquo; <a href="./?v=more">More</a> &rsaquo; Readers</p>
<?php
// sql dotaz #9
	$sqlquery_9 = "SELECT * FROM Readers ORDER BY id ASC";
// ochrana dotazu #9
	if ($result_9 = mysqli_query($connection, $sqlquery_9)) {
// kontrola, zda jsou dostupna data
		if (mysqli_num_rows($result_9) > 0) {
?>
		<div id="table-container">
			<table id="data-table">
				<thead>
					<tr>
						<th class="th-12">Reader</th>
						<th class="th-12">Protocol</th>
						<th class="th-12">Address</th>
						<th class="th-12">Active</th>
						<th class="th-12">Output</th>
						<th class="th-12">Pulse time</th>
						<th class="th-12">Timezone</th>
						<th class="th-12">Monitor (curr. state)</th>
						<th class="th-12">Monitor (default)</th>
						<th class="th-12">Open time (max)</th>
						<th class="th-12">Has monitor</th>
						<th class="th-12">Management</th>
					</tr>
				</thead>
				<tbody>
<?php
// cyklus pro vypis dat #9
			while ($row_9 = mysqli_fetch_array($result_9)) {
				echo '<tr>';
				echo '<td class="td-centered"><a href="./?v=readers;s=edit;i=' . htmlspecialchars($row_9['id'], ENT_QUOTES, 'UTF-8') . '">Reader [' . htmlspecialchars($row_9['id'], ENT_QUOTES, 'UTF-8') . ']</a></td>';
				echo '<td class="td-centered">' . htmlspecialchars($row_9['protocol'], ENT_QUOTES, 'UTF-8') . '</td>';
				echo '<td>' . htmlspecialchars($row_9['address'], ENT_QUOTES, 'UTF-8') . '</td>';
				echo '<td class="td-centered">' . htmlspecialchars($row_9['active'], ENT_QUOTES, 'UTF-8') . '</td>';
				echo '<td class="td-centered">' . ($row_9['output'] == 'relay' ? 'RELAY' : ($row_9['output'] == 'gpio' ? 'GPIO' : 'Undefined output!')) . '</td>';
				echo '<td class="td-centered">' . htmlspecialchars($row_9['pulse_time'], ENT_QUOTES, 'UTF-8') . '</td>';
				$zone_number_9 = htmlspecialchars($row_9['sys_plan'], ENT_QUOTES, 'UTF-8');
				$zone_name_9 = get_zone_name_by_number($connection, $zone_number_9);
				echo '<td class="td-centered">' . ($zone_name_9 ? 'Timezone [' . $zone_number_9 . '] &ndash; ' . htmlspecialchars($zone_name_9, ENT_QUOTES, 'UTF-8') : 'There is no zone!') . '</td>';
				echo '<td class="td-centered">' . htmlspecialchars($row_9['monitor'], ENT_QUOTES, 'UTF-8') . '</td>';
				echo '<td class="td-centered">' . htmlspecialchars($row_9['monitor_default'], ENT_QUOTES, 'UTF-8') . '</td>';
				echo '<td class="td-centered">' . htmlspecialchars($row_9['max_open_time'], ENT_QUOTES, 'UTF-8') . '</td>';
				echo '<td class="td-centered">' . htmlspecialchars($row_9['has_monitor'], ENT_QUOTES, 'UTF-8') . '</td>';
				echo '<td class="td-centered"><a href="./?v=readers;s=edit;i=' . htmlspecialchars($row_9['id'], ENT_QUOTES, 'UTF-8') . '">Edit</a></td>';
				echo '</tr>' . "\n";
			}
?>
				</tbody>
			</table>
		</div>
<?php
			mysqli_free_result($result_9);
		} else {
			echo '<p>No data to display!</p>';
		}
	} else {
		echo '<p>Unable to process query! "' . htmlspecialchars($sqlquery_9, ENT_QUOTES, 'UTF-8') . '", Error! ' . htmlspecialchars(mysqli_error($connection), ENT_QUOTES, 'UTF-8') . '</p>';
	}
}
// ========== end pohled READERS - list
// ========== begin pohled READERS - editace
if ($key_view == $stat_view_array['g'] && $key_cond == $stat_cond_array['e']) {
?>
		<h2>Edit Reader</h2>
		<p><a href="./?v=home">Dashboard</a> &rsaquo; <a href="./?v=more">More</a> &rsaquo; <a href="./?v=readers" onclick="return confirm('Are you sure you want to leave without saving?');">Readers</a> &rsaquo; Edit Reader</p>
		<div id="form-container">
			<form action="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>" method="post" enctype="application/x-www-form-urlencoded" autocomplete="off">
				<input type="hidden" name="id" value="<?php echo htmlspecialchars($row_select['id'], ENT_QUOTES, 'UTF-8'); ?>">
				<label for="foreditprotocol">Protocol (read only)</label>
				<input type="text" id="foreditprotocol" name="edit-protokolu" value="<?php echo htmlspecialchars($row_select['protocol'], ENT_QUOTES, 'UTF-8'); ?>" readonly="readonly">
				<label for="foreditaddress">Address (read only)</label>
				<input type="text" id="foreditaddress" name="edit-adresy" value="<?php echo htmlspecialchars($row_select['address'], ENT_QUOTES, 'UTF-8'); ?>" readonly="readonly">
				<label for="foreditactive">Active (read only)</label>
				<input type="text" id="foreditactive" name="edit-aktivace" value="<?php echo htmlspecialchars($row_select['active'], ENT_QUOTES, 'UTF-8'); ?>" readonly="readonly">
				<label for="foreditoutput">Output <span class="required">*</span></label>
				<select id="foreditoutput" name="edit-outputu" required="required">
					<option value="">&ndash;</option>
					<option value="relay" <?php echo $row_select['output'] == 'relay' ? 'selected="selected"' : ''; ?>>RELAY</option>
					<option value="gpio" <?php echo $row_select['output'] == 'gpio' ? 'selected="selected"' : ''; ?>>GPIO</option>
				</select>
				<label for="foreditpulse">Pulse time (ms) <span class="required">*</span></label>
				<input type="text" id="foreditpulse" name="edit-pulzu" value="<?php echo htmlspecialchars($row_select['pulse_time'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="50">
				<label for="foreditcasplan">Timezones <span class="required">*</span></label>
				<select id="foreditcasplan" name="edit-cas-planu" required="required">
					<option value="0">No timezone [0]</option>
<?php
	$sqlquery_timezone = "SELECT Cislo, Nazev FROM CasovePlany ORDER BY Cislo ASC";
	$result_timezone = mysqli_query($connection, $sqlquery_timezone);
	while ($row_timezone = mysqli_fetch_assoc($result_timezone)) {
		$timezone_value = htmlspecialchars($row_timezone['Cislo'], ENT_QUOTES, 'UTF-8');
		$timezone_name = htmlspecialchars($row_timezone['Nazev'], ENT_QUOTES, 'UTF-8');
		echo '<option value="' . $timezone_value . '"' . ($row_select['sys_plan'] == $timezone_value ? ' selected="selected"' : '') . '>Timezone [' . $timezone_value . '] &ndash; ' . $timezone_name . '</option>' . "\n";
	}
?>
				</select>
				<label for="foreditmonitor">Monitor (curr. state)<span class="required">*</span></label>
				<input type="text" id="foreditmonitor" name="edit-monitor" value="<?php echo htmlspecialchars($row_select['monitor'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="1">
				<label for="foreditmonitordef">Monitor (default) <span class="required">*</span></label>
				<input type="text" id="foreditmonitordef" name="edit-monitordef" value="<?php echo htmlspecialchars($row_select['monitor_default'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="1">
				<label for="foreditopentime">Open time (max) (ms) <span class="required">*</span></label>
				<input type="text" id="foreditopentime" name="edit-opentime" value="<?php echo htmlspecialchars($row_select['max_open_time'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="50">
				<label for="foredithasmonitor">Has monitor <span class="required">*</span></label>
				<input type="text" id="foredithasmonitor" name="edit-hasmonitor" value="<?php echo htmlspecialchars($row_select['has_monitor'], ENT_QUOTES, 'UTF-8'); ?>" maxlength="50">
				<div></div>
				<button type="submit" name="edit-ctecka" onclick="return confirm('Are you sure you want to edit this listing?');">Commit changes</button>
			</form>
		</div>
		<p><span class="required">* Required</span></p>
		<p><a href="./?v=readers" onclick="return confirm('Are you sure you want to leave without saving?');">&lsaquo; Back on Readers</a></p>
<?php
}
// ========== end pohled READERS - editace
// ========== end pohled READERS
// ========== begin pohled LOGS
if ($key_view == $stat_view_array['v']) {
?>
		<h2>Logs</h2>
		<p><a href="./?v=home">Dashboard</a> &rsaquo; <a href="./?v=more">More</a> &rsaquo; Logs</p>
<?php
// sql dotaz #6
	$sqlquery_6 = "SELECT * FROM logs ORDER BY ts DESC LIMIT $max_rows_value";
// ochrana dotazu #6
	if ($result_6 = mysqli_query($connection, $sqlquery_6)) {
// roztrhani vysledku sql dotazu #6
		if (mysqli_num_rows($result_6) > 0) {
?>
		<div rows="5" id="textarea-container">
			<pre><?php
// cyklus pro vypis dat #6
			while ($row_6 = mysqli_fetch_array($result_6)) {
				echo $row_6['ts'] . ' ';
				echo $row_6['severity'] . ' ';
				echo $row_6['message'];
				echo "\n";
			}
?></pre>
		</div>
<?php
// uvolneni pameti #6
			mysqli_free_result($result_6);
		} else {
			echo '<p>No data to display!</p>';
		}
	} else {
		echo '<p>Unable to process query! "' . $sqlquery_6 . '", Error! ' . mysqli_error($connection) . '</p>';
	}
}
// ========== end pohled LOGS
?>
	</div>
	<div id="footer">
<?php
// ========== begin FOOTER
// ========== begin JINE POHLEDY NEZ LOGIN
if ($key_view != $stat_view_array['l']) {
?>
		<ul id="footer-navigation">
			<li id="navigation-home"><a href="./?v=home">Dash</a></li>
			<li id="navigation-access"><a href="./?v=access">Access</a></li>
			<li id="navigation-cards"><a href="./?v=cards">Cards</a></li>
			<li id="navigation-users"><a href="./?v=users">Users</a></li>
			<li id="navigation-more"><a href="./?v=more">More</a></li>
		</ul>
<?php
}
// ========== end JINE POHLEDY NEZ LOGIN
// ========== end FOOTER
?>
	</div>
</div>
<div id="infobox" style="display: none;">
	<div id="content-mini">
		<h2>Information</h2>
<?php
if ($key_view != $stat_view_array['l']) {
?>
		<p>Data from <a href="./?v=running">Running</a></p>
<?php
}
// sql dotaz #8
	$sqlquery_8 = "SELECT * FROM running order by property ASC";
// ochrana dotazu #8
	if ($result_8 = mysqli_query($connection, $sqlquery_8)) {
// roztrhani vysledku sql dotazu #8
		if (mysqli_num_rows($result_8) > 0) {
?>
		<div id="table-container-mini">
			<table>
				<thead>
					<tr>
						<th class="th-3">Property</th>
						<th class="th-3">Value</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="td-upper">Date, time</td>
						<td><span id="datetime1"></span></td>
					</tr>
<?php
// cyklus pro vypis dat #8
			while ($row_8 = mysqli_fetch_array($result_8)) {
// zkontrolujeme, zda je hodnota ve sloupci property bud MyIP nebo Version
				if ($row_8['property'] === 'MyIP' || $row_8['property'] === 'Version') {
					echo "\t\t\t\t\t";
					echo '<tr>';
					echo '<td class="td-upper">' . $row_8['property'] . '</td>';
					echo '<td>' . $row_8['value'] . '</td>';
					echo '</tr>';
					echo "\n";
				}
			}
?>
				</tbody>
			</table>
		</div>
<?php
// uvolneni pameti #8
			mysqli_free_result($result_8);
		} else {
			echo '<p>No data to display!</p>';
		}
	} else {
		echo '<p>Unable to process query! "' . $sqlquery_8 . '", Error! ' . mysqli_error($connection) . '</p>';
	}
?>
	</div>
</div>
<script src="<?php
echo fileVersioning('./js/meritaccess-body.min.js');
?>"></script>
</body>
</html><?php
// ukonceni spojeni
mysqli_close($connection);
?>