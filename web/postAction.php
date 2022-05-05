<?php
session_start();

// DB Klasse initialisieren
require_once 'DB.class.php';
$db = new DB();

$tblName = 'images';

// Upload-Directory für Bilder
$uploadDir = "uploads/images/";

// Erlaubte Bildformate
$allowTypes = array('jpg','png','jpeg','gif');

// Redirect URL setzen
$redirectURL = 'manage.php';

$statusMsg = '';
$sessData = array();
$statusType = 'danger';
if(isset($_POST['imgSubmit'])){
	
	 // Redirect URL setzen
    $redirectURL = 'addEdit.php';

    // Eingegebene Bilddaten übernehmen
    $image	= $_FILES['image'];
	$title	= $_POST['title'];
	$id		= $_POST['id'];
    
    // Eingegebene Userdaten übernehmen
    $imgData = array(
        'title'  => $title
    );
    
    // Eingegebene Daten in Session speichern
    $sessData['postData'] = $imgData;
    $sessData['postData']['id'] = $id;
    
    // ID query string
    $idStr = !empty($id)?'?id='.$id:'';
    
    // Überprüft ob alles ausgefüllt ist
    if((!empty($image['name']) && !empty($title)) || (!empty($id) && !empty($title))){
		
		if(!empty($image)){
			$fileName = basename($image["name"]);
			$targetFilePath = $uploadDir . $fileName;
			$fileType = pathinfo($targetFilePath,PATHINFO_EXTENSION);
			
			if(in_array($fileType, $allowTypes)){
				// Upload auf Server
				if(move_uploaded_file($image["tmp_name"], $targetFilePath)){
					$imgData['file_name'] = $fileName;
				}else{
					$statusMsg = "Beim Upload ist ein Problem aufgetreten";
				}
			}else{
				$statusMsg = "Nur JPG, JPEG, PNG, & GIF - Dateien sind erlaubt";
			}
		}

		if(!empty($id)){
			// alter Dateiname
			$conditions['where'] = array(
				'id' => $_GET['id'],
			);
			$conditions['return_type'] = 'single';
			$prevData = $db->getRows('images', $conditions);
			
			// Daten updaten
			$condition = array('id' => $id);
			$update = $db->update($tblName, $imgData, $condition);
			
			if($update){
				// alte Datei löschen
				if(!empty($imgData['file_name'])){
					@unlink($uploadDir.$prevData['file_name']);
				}
				
				$statusType = 'success';
				$statusMsg = 'Bilddaten wurden erfolgreich geupdated';
				$sessData['postData'] = '';
				
				$redirectURL = 'manage.php';
			}else{
				$statusMsg = 'Ein Problem ist aufgetreten, bitte erneut versuchen';
				// Redirect URL setzen
				$redirectURL .= $idStr;
			}
		}elseif(!empty($imgData['file_name'])){
			// Daten einfügen
			$insert = $db->insert($tblName, $imgData);
			
			if($insert){
				$statusType = 'success';
				$statusMsg = 'Bild wurde erfolgreich hochgeladen';
				$sessData['postData'] = '';
				
				$redirectURL = 'manage.php';
			}else{
				$statusMsg = 'Ein Problem ist aufgetreten, bitte erneut versuchen';
			}
		}
	}else{
		$statusMsg = 'Bitte alle Felder ausfüllen';
	}
	
	// Status
	$sessData['status']['type'] = $statusType;
    $sessData['status']['msg']  = $statusMsg;
}elseif(($_REQUEST['action_type'] == 'block') && !empty($_GET['id'])){
    // Daten updaten
	$imgData = array('status' => 0);
    $condition = array('id' => $_GET['id']);
    $update = $db->update($tblName, $imgData, $condition);
    if($update){
        $statusType = 'success';
        $statusMsg  = 'Bild wurde erfolgreich deaktiviert';
    }else{
        $statusMsg  = 'Ein Problem ist aufgetreten, bitte erneut versuchen';
    }
	
	// Status
	$sessData['status']['type'] = $statusType;
    $sessData['status']['msg']  = $statusMsg;
}elseif(($_REQUEST['action_type'] == 'unblock') && !empty($_GET['id'])){
    // Daten updaten
	$imgData = array('status' => 1);
    $condition = array('id' => $_GET['id']);
    $update = $db->update($tblName, $imgData, $condition);
    if($update){
        $statusType = 'success';
        $statusMsg  = 'Bild wurde erfolgreich aktiviert';
    }else{
        $statusMsg  = 'Ein Problem ist aufgetreten, bitte erneut versuchen';
    }
	
	// Status
	$sessData['status']['type'] = $statusType;
    $sessData['status']['msg']  = $statusMsg;
}elseif(($_REQUEST['action_type'] == 'delete') && !empty($_GET['id'])){
	// alter Dateiname
	$conditions['where'] = array(
		'id' => $_GET['id'],
	);
	$conditions['return_type'] = 'single';
	$prevData = $db->getRows('images', $conditions);
				
    // Daten löschen
    $condition = array('id' => $_GET['id']);
    $delete = $db->delete($tblName, $condition);
    if($delete){
		// alte Datei löschen
		@unlink($uploadDir.$prevData['file_name']);
		
        $statusType = 'success';
        $statusMsg  = 'Bild erfolgreich gelöscht';
    }else{
        $statusMsg  = 'Ein Problem ist aufgetreten, bitte erneut versuchen';
    }
	
	// Status
	$sessData['status']['type'] = $statusType;
    $sessData['status']['msg']  = $statusMsg;
}

// Status in session speichern
$_SESSION['sessData'] = $sessData;
	
// User wird redirected
header("Location: ".$redirectURL);
exit();
?>