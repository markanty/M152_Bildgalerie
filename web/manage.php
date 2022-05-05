<?php
session_start();

// DB Klasse initialisieren
require_once 'DB.class.php';
$db = new DB();

// Holt Bilddaten aus der Datenbank
$images = $db->getRows('images');

// Holt Session Data
$sessData = !empty($_SESSION['sessData'])?$_SESSION['sessData']:'';

// Holt status aus session
if(!empty($sessData['status']['msg'])){
    $statusMsg = $sessData['status']['msg'];
    $statusMsgType = $sessData['status']['type'];
    unset($_SESSION['sessData']['status']);
}
?>
<!DOCTYPE html>
<html lang="de-CH">
<head>
<title>M152 Bildgalerie Management</title>
<meta charset="utf-8">

<!-- Bootstrap library -->
<link rel="stylesheet" href="bootstrap/bootstrap.min.css">

<!-- Stylesheet file -->
<link rel="stylesheet" type="text/css" href="css/style.css">

</head>
<body>
<div class="container">
	<h1>M152 Bildgalerie Management</h1>
	<hr>
	
	<!-- Statusmeldungen -->
    <?php if(!empty($statusMsg)){ ?>
    <div class="col-xs-12">
        <div class="alert alert-<?php echo $statusMsgType; ?>"><?php echo $statusMsg; ?></div>
    </div>
    <?php } ?>

    <!-- Button zum Bilderupload -->
	<div class="row">
        <div class="col-md-12 head">
            <h5>Bilder</h5>
            <div class="float-right">
                <a href="addEdit.php" class="btn btn-success"><i class="plus"></i> Bild hochladen</a>
            </div>
        </div>
		
        <!-- Bildertabelle -->
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th width="5%"></th>
                    <th width="12%">Bild</th>
                    <th width="40%">Titel</th>
                    <th width="17%">Erstellt</th>
					<th width="8%">Status</th>
                    <th width="18%">Aktion</th>
                </tr>
            </thead>
            <tbody>
                <?php
				if(!empty($images)){
					foreach($images as $row){
						$statusLink = ($row['status'] == 1)?'postAction.php?action_type=block&id='.$row['id']:'postAction.php?action_type=unblock&id='.$row['id'];
						$statusTooltip = ($row['status'] == 1)?'Klicken zum deaktivieren':'Klicken zum aktivieren';
				?>
                <tr>
                    <td><?php echo '#'.$row['id']; ?></td>
                    <td><img src="<?php echo 'uploads/images/'.$row['file_name']; ?>" alt="" /></td>
                    <td><?php echo $row['title']; ?></td>
					<td><?php echo $row['created']; ?></td>
                    <td><a href="<?php echo $statusLink; ?>" title="<?php echo $statusTooltip; ?>"><span class="badge <?php echo ($row['status'] == 1)?'badge-success':'badge-danger'; ?>"><?php echo ($row['status'] == 1)?'Aktiv':'Inaktiv'; ?></span></a></td>
                    <td>
                        <a href="addEdit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning">bearbeiten</a>
                        <a href="postAction.php?action_type=delete&id=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Sind Sie sicher, dass Sie das Bild löschen wollen?')?true:false;">löschen</a>
                    </td>
                </tr>
                <?php } }else{ ?>
                <tr><td colspan="6">Kein(e) Bild(er) gefunden...</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Button zur Bildgalerie -->
    <div class="row">
        <div class="col-md-12 head">
            <div class="float-right">
                <a href="index.php" class="btn btn-success"> Zurück zur Galerie</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>