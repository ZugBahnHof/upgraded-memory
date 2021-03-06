<?php
session_start();
require_once( "inc/config.inc.php" );
require_once( "inc/functions.inc.php" );

//Überprüfe, dass der User eingeloggt ist
//Der Aufruf von check_user() muss in alle internen Seiten eingebaut sein
$user = check_user();

$a00="active";
$r00="&raquo;";
$site_color = "cyan";
$site_color_text = "cyan-text";
$site_color_html = "#00bcd4";
$site_description = "Einstellungen - ";
include "inc/header.inc.php";

if ( isset( $_GET['save'] ) ) {
	$save = $_GET['save'];

	if ( $save == 'personal_data' ) {
		$vorname  = trim( $_POST['vorname'] );
		$nachname = trim( $_POST['nachname'] );

		if ( $vorname == "" || $nachname == "" ) {
			$error_msg = "Bitte Vor- und Nachname ausfüllen.";
		} else {
			$statement = $pdo->prepare( "UPDATE users SET vorname = :vorname, nachname = :nachname, updated_at=NOW() WHERE id = :userid" );
			$result    = $statement->execute( array(
				'vorname'  => $vorname,
				'nachname' => $nachname,
				'userid'   => $user['id']
			) );

			$success_msg = "Daten erfolgreich gespeichert.";
		}
	} else if ( $save == 'email' ) {
		$passwort = $_POST['passwort'];
		$email    = trim( $_POST['email'] );
		$email2   = trim( $_POST['email2'] );

		if ( $email != $email2 ) {
			$error_msg = "Die eingegebenen E-Mail-Adressen stimmten nicht überein.";
		} else if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			$error_msg = "Bitte eine gültige E-Mail-Adresse eingeben.";
		} else if ( ! password_verify( $passwort, $user['passwort'] ) ) {
			$error_msg = "Bitte korrektes Passwort eingeben.";
		} else {
			$statement = $pdo->prepare( "UPDATE users SET email = :email WHERE id = :userid" );
			$result    = $statement->execute( array( 'email' => $email, 'userid' => $user['id'] ) );

			$success_msg = "E-Mail-Adresse erfolgreich gespeichert.";
		}

	} else if ( $save == 'passwort' ) {
		$passwortAlt  = $_POST['passwortAlt'];
		$passwortNeu  = trim( $_POST['passwortNeu'] );
		$passwortNeu2 = trim( $_POST['passwortNeu2'] );

		if ( $passwortNeu != $passwortNeu2 ) {
			$error_msg = "Die eingegebenen Passwörter stimmten nicht überein.";
		} else if ( $passwortNeu == "" ) {
			$error_msg = "Das Passwort darf nicht leer sein.";
		} else if ( ! password_verify( $passwortAlt, $user['passwort'] ) ) {
			$error_msg = "Bitte korrektes Passwort eingeben.";
		} else {
			$passwort_hash = password_hash( $passwortNeu, PASSWORD_DEFAULT );

			$statement = $pdo->prepare( "UPDATE users SET passwort = :passwort WHERE id = :userid" );
			$result    = $statement->execute( array( 'passwort' => $passwort_hash, 'userid' => $user['id'] ) );

			$success_msg = "Passwort erfolgreich gespeichert.";
		}

	}
}

$user = check_user();

?>


<h1 class="<?php echo $site_color_text; ?>">Einstellungen</h1>

<?php
if ( isset( $success_msg ) && ! empty( $success_msg ) ):
	?>
    <div class="alert alert-success">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
		<?php echo $success_msg; ?>
    </div>
<?php
endif;
?>

<?php
if ( isset( $error_msg ) && ! empty( $error_msg ) ):
	?>
    <div class="alert alert-danger">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
		<?php echo $error_msg; ?>
    </div>
<?php
endif;
?>

<div class="row">
    <div class="col s12">
        <ul class="tabs tabs_js <?php echo $site_color . "-text"; ?>">
            <li class="tab col s4"><a class="<?php echo $site_color . "-text"; ?> active" href="#data">Persönliche
                    Daten</a></li>
            <li class="tab col s4"><a class="<?php echo $site_color . "-text"; ?>" href="#email">E-Mail</a></li>
            <li class="tab col s4"><a class="<?php echo $site_color . "-text"; ?>" href="#passwort">Passwort</a></li>
            <div class="indicator <?php echo $site_color; ?>" style="z-index:1"></div>
        </ul>
    </div>

    <!-- Persönliche Daten-->
    <div class="row" id="data">
        <form action="?save=personal_data" method="post" class="col s12">
            <h5>Zum Änderen deiner Persönlichen Daten gib bitte die neuen Daten, sowie deine E-Mail-Adresse ein.</h5>

            <div class="input-field col s12">
                <input class="validate" id="inputEmail" name="email" type="email"
                       value="<?php echo htmlentities( $user['email'] ); ?>" required>
                <label for="inputEmail">E-Mail</label>
            </div>

            <div class="input-field col s12">
                <input class="validate" id="inputVorname" name="vorname" type="text"
                       value="<?php echo htmlentities( $user['vorname'] ); ?>" required>
                <label for="inputVorname">Vorname</label>
            </div>

            <div class="input-field col s12">
                <input class="validate" id="inputNachname" name="nachname" type="text"
                       value="<?php echo htmlentities( $user['nachname'] ); ?>" required>
                <label for="inputNachname">Nachname</label>
            </div>

            <div class=" col s12">
                <button type="submit" class="<?php echo $site_color; ?> btn btn-primary">Speichern</button>
            </div>
        </form>
    </div>

    <!-- Änderung der E-Mail-Adresse -->
    <div class="row" id="email">
        <form action="?save=email" method="post" class="col s12">
            <h5>Zum Änderen deiner E-Mail-Adresse gib bitte dein aktuelles Passwort sowie die neue E-Mail-Adresse
                ein.</h5>

            <div class="input-field col s12">
                <input class="validate" id="inputPasswort" name="passwort" type="password" required>
                <label for="inputPasswort">Passwort</label>
            </div>

            <div class="input-field col s12">
                <input class="validate" id="inputEmail" name="email" type="email"
                       value="<?php echo htmlentities( $user['email'] ); ?>" required>
                <label for="inputEmail">E-Mail</label>
            </div>

            <div class="input-field col s12">
                <input class="validate" id="inputEmail2" name="email2" type="email" required>
                <label for="inputEmail2">E-Mail (wiederholen)</label>
            </div>

            <div class=" col s12">
                <button type="submit" class="<?php echo $site_color; ?> btn btn-primary">Speichern</button>
            </div>
        </form>
    </div>

    <!-- Änderung des Passworts -->
    <div class="row" id="passwort">
        <form action="?save=passwort" method="post" class="col s12">
            <h5>Zum Änderen deines Passworts gib bitte dein aktuelles Passwort sowie das neue Passwort ein.</h5>

            <div class="input-field col s12">
                <input class="validate" id="inputPasswort" name="passwortAlt" type="password" required>
                <label for="inputPasswort">Altes Passwort</label>
            </div>

            <div class="input-field col s12">
                <input class="validate" id="inputPasswortNeu" name="passwortNeu" type="password" required>
                <label for="inputPasswortNeu">Neues Passwort</label>
            </div>


            <div class="input-field col s12">
                <input class="validate" id="inputPasswortNeu2" name="passwortNeu2" type="password" required>
                <label for="inputPasswortNeu2">Neues Passwort (wiederholen)</label>
            </div>

            <div class="form-group">
                <div class="col s12">
                    <button type="submit" class="<?php echo $site_color; ?> btn btn-primary">Speichern</button>
                </div>
        </form>
    </div>

</div>

<?php
include( "inc/footer.inc.php" )
?>
