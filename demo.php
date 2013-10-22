<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php
ini_set('error_reporting', E_ALL);
require 'petrovich.class.php';
require 'petrovich.rules.php';
//'nominative', 'genitive', 'dative', 'accusative', 'instrumental', 'prepositional'
$petrovich = new Petrovich('genitive', 'Иванов', 'Иван', 'Иванович');
echo $petrovich->getGender() . '<br />';

$cases = array('nominative', 'genitive', 'dative', 'accusative', 'instrumental', 'prepositional');
$names = array('lastname', 'firstname', 'middlename');
for ($i = 0; $i < count($cases); $i++) {
	for ($o = 0; $o < count($names); $o++) {
		echo $petrovich->inflect( $petrovich->$names[$o], $cases[$i], $petrovich->gender, $rules[$names[$o]] ) . ' ';
	}
	echo '<br />';
}
