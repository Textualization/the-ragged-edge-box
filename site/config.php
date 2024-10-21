<?php
$changed = false;
$config = json_decode(\file_get_contents("../data/config.json"), true);
if(isset($_POST['endpoint'])) {
    $config['endpoint'] = $_POST['endpoint'];
    $changed = true;
}
function recrm($dir) {
    $files = \scandir($dir);
    foreach ($files as $f) { 
       if ($f == "." || $f == "..")
           continue;
       $ff = "$dir/$f";
       if (\is_dir($ff) && ! \is_link($ff))
           recrm($ff);
       else
           \unlink($ff); 
    } 
    \rmdir($dir); 
}
if(isset($_POST['deldb'])) {
    $deldb = $_POST['deldb'];
    $prefix = __DIR__."/../data/".$config["databases"][$deldb]["file"];
    unlink("$prefix.db");
    recrm($prefix);
    unset($config["databases"][$deldb]);
    if(count($config["databases"]) == 0) {
        unset($config["default"]);
    }else{
        if($config["default"] == $deldb) {
            $config["default"] = 0;
        }elseif($config["default"] > $deldb) {
            $config["default"]--;
        }
    }
    $changed = true;
}
if($changed){
    \file_put_contents("../data/config.json", json_encode($config, JSON_PRETTY_PRINT));
}    
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>The RAGged Edge Box</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
  <style>
    .container {
    background: url("./config.png") no-repeat center;
    margin:auto auto;
    }
  </style>
</head>
<body>
  <header class="bg-primary text-white text-center py-3">
    <h1 class="d-inline-block align-text-top"> <img src="logo.png" alt="Logo" width="100" height="100" class="d-inline-block align-text-middle">The RAGged Edge Box</h1>
    <ol class="breadcrumb py-0">
      <li class="breadcrumb-item "><a class=" text-info" href="index.php">Home</a></li>
      <li class="breadcrumb-item text-success active">Configuration</li>
    </ol>
  </header>
  
  <main id="main" class="container my-5">
    <div class="row">
      <div class="col-12 text-primary py-3 mb-2">
        <h1>Configuration</h1>
      </div>
    </div>
    <div class="row">
      <div class="col-12 text-primary py-3 mb-2">
        <h2>LLM Endpoint</h2>
        <form method="POST" enctype="multipart/form-data">
<?php
   $endpoint = $config['endpoint'] ?? 'http://127.0.0.1:8091';
   echo '<input type="text" name="endpoint" class="form-control" aria-label="URL of LLM endpoint" value="' . $endpoint . '"/>';
?>
          <input type="submit" class="btn btn-primary ps-2" value="Change"/>
        </form>
      </div>
    </div>
<?php
if(count($config["databases"]) > 0){
?>
    <div class="row">
      <div class="col-12 text-primary py-3 mb-4">
        <h2>Delete Databases</h2>
        <ul>
<?php
  $idx = 0;
  foreach($config["databases"] as $db) {           
    echo '<li><form method="POST"  enctype="multipart/form-data">';
    echo '<input type="hidden" name="deldb" value="'.$idx.'"/>';
    echo $db['name'].' <input type="submit" class="btn btn-danger ps-2" value="Delete"/></form></li>';
}
?>
        </ul>
      </div>
<?php
}
?>
    </main>

    <footer class="position-fixed bottom-0 end-0 p-3">
        <a href="#" class="text-decoration-none text-dark me-3">
            <i class="bi bi-gear"></i>
        </a>
        <a href="help.php" class="text-decoration-none text-dark">
            <i class="bi bi-question-circle"></i>
        </a>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.min.js"></script>
</body>
</html>
