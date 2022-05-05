<?php
session_start();

$postData = $imgData = array();

// Holt Session Data
$sessData = !empty($_SESSION['sessData'])?$_SESSION['sessData']:'';

// Holt status aus session
if(!empty($sessData['status']['msg'])){
    $statusMsg = $sessData['status']['msg'];
    $statusMsgType = $sessData['status']['type'];
    unset($_SESSION['sessData']['status']);
}

// Holt postData aus session
if(!empty($sessData['postData'])){
    $postData = $sessData['postData'];
    unset($_SESSION['sessData']['postData']);
}

// Holt Bilddaten
if(!empty($_GET['id'])){
    // DB Klasse initialisieren
    require_once 'DB.class.php';
	$db = new DB();
	
    $conditions['where'] = array(
        'id' => $_GET['id'],
    );
    $conditions['return_type'] = 'single';
    $imgData = $db->getRows('images', $conditions);
}

// Bereits gefüllte Daten
$imgData = !empty($postData)?$postData:$imgData;

// Erstellung der Action Labels
$actionLabel = !empty($_GET['id'])?'Bearbeiten':'Bild hochladen';
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
    <h1><?php echo $actionLabel; ?></h1>
	<hr>
    
    <!-- Statusmeldungen -->
    <?php if(!empty($statusMsg)){ ?>
    <div class="col-xs-12">
        <div class="alert alert-<?php echo $statusMsgType; ?>"><?php echo $statusMsg; ?></div>
    </div>
    <?php } ?>
    
    <div class="row">
        <div class="col-md-6">
            <form method="post" action="postAction.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Bild</label>
					<?php if(!empty($imgData['file_name'])){ ?>
						<img src="uploads/images/<?php echo $imgData['file_name']; ?>">
					<?php } ?>
                    <input type="file" name="image" class="form-control" >
                </div>
                <div class="form-group">
                    <label>Titel</label>
                    <input type="text" name="title" class="form-control" placeholder="Bildtitel eingeben" value="<?php echo !empty($imgData['title'])?$imgData['title']:''; ?>" >
                </div>
                <a href="manage.php" class="btn btn-secondary">Zurück</a>
				<input type="hidden" name="id" value="<?php echo !empty($imgData['id'])?$imgData['id']:''; ?>">
                <input type="submit" name="imgSubmit" class="btn btn-success" value="Hochladen">
            </form>
        </div>
    </div>
</div>
</body>
</html>