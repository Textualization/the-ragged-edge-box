<?php
$config = json_decode(\file_get_contents("../data/config.json"), true);
if(isset($_GET["download"])) {
    $fname = $_GET["download"];
    [ $files_db, $files_section ] = \explode(":", $_GET["files"]);
    if($files_section === "/main") {
        $file = __DIR__."/../data/".$config["databases"][$files_db]["file"]."/".$fname;
    }else{
        $file = __DIR__."/../data/".$config["databases"][$files_db]["file"]."/".$files_section."/".$fname;
    }
    header('Content-type:  binary/octet-stream');
    header('Content-Disposition: attachment; filename="'.$fname.'"');
    header("Content-Length: " . filesize($file));
    $fp = \fopen($file, 'rb');
    \fpassthru($fp);
    exit();
}
//TODO delete
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
</head>
<body>
    <header class="bg-primary text-white text-center py-3">
        <h1 class="d-inline-block align-text-top"> <img src="logo.png" alt="Logo" width="100" height="100" class="d-inline-block align-text-middle">The RAGged Edge Box</h1>
    </header>

    <main class="container my-5">
        <div class="row justify-content-center">
          <div class="col-md-8">

<ul class="nav nav-tabs" role="tablist">
  <li class="nav-item" role="presentation">
<?php
  if(isset($_GET["files"])) {
      echo '<a class="nav-link" data-bs-toggle="tab" href="#search" aria-selected="false" role="tab" tabindex="1">Search</a>';
  }else{
      echo '<a class="nav-link active" data-bs-toggle="tab" href="#search" aria-selected="true" role="tab" tabindex="1">Search</a>';
  }
?>
  </li>
  <li class="nav-item" role="presentation">
    <a class="nav-link" data-bs-toggle="tab" href="#upload" aria-selected="false" role="tab" tabindex="2">Upload</a>
  </li>
  <li class="nav-item" role="presentation">
<?php
  if(isset($_GET["files"])) {
      echo '<a class="nav-link active" data-bs-toggle="tab" href="#files" aria-selected="true" role="tab" tabindex="3">Files</a>';
  }else{
      echo '<a class="nav-link" data-bs-toggle="tab" href="#files" aria-selected="false" role="tab" tabindex="3">Files</a>';
  }
?>
  </li>
</ul>
<div id="myTabContent" class="tab-content">
<?php
   if(isset($_GET["files"])) {
       echo '  <div class="tab-pane fade" id="search" role="tabpanel">';
   }else{
       echo '  <div class="tab-pane fade active show" id="search" role="tabpanel">';
   }
?>
    <form action="search.php" method="POST">
      <div class="input-group mb-3 pt-5 pb-2">
          <input type="text" name="query" class="form-control" placeholder="Type your query or question here..." aria-label="Search" />
          <input type="submit" class="btn btn-primary" name="search" value="Search" />
          <input type="submit" class="btn btn-success" name="answer" value="Answer" />
      </div>
      <label for="btn-group-search-idx">Index</label>
      <div class="input-group mb-3 pt-1">
        <div class="btn-group" id="btn-group-search-idx" role="group" aria-label="Index">
<?php
  $idx=0;
  foreach($config["databases"] as $db) {
      $name = 'searchidx'.$idx;
      $selected = $idx==$config["default"]?"checked":"";
      echo '<input type="radio" class="btn-check" name="searchidxradio" id="'.$name.'" value="'.$idx.'" '.$selected.'>';
      echo '<label class="btn btn-outline-primary" for="'.$name.'">'.$db["name"].'</label>';
      $idx++;
}
?>
        </div> <!-- btn-group -->
      </div> <!-- input-group -->
    </form>
  </div> <!-- tab -->
  <div class="tab-pane fade" id="upload" role="tabpanel">
    <form action="upload.php" method="POST" enctype="multipart/form-data">
      <div class="input-group text-center mb-3 pt-5">
        <input name="upload" type="file" size="60" aria-label="Upload" />
        <input type="submit" class="btn btn-primary ps-2" value="Upload Document"/>
      </div>
      <div class="input-group mb-3 pt-2">
        <div class="input-group-prepend"><span class="input-group-text"><label for="section">Section</label></span></div>
        <input name="section" type="text" placeholder="leave empty for default" size="20" aria-label="Section" />
      </div>                                                        
      <label for="btn-group-upload-idx">Index</label>
      <div class="input-group mb-3 pt-1">
        <div class="btn-group" id="btn-group-upload-idx" role="group" aria-label="Index">
<?php
  $idx=0;
  foreach($config["databases"] as $db) {
      $name = 'uploadidx'.$idx;
      $selected = $idx==$config["default"]?"checked":"";
      echo '<input type="radio" class="btn-check" name="uploadidxradio" id="'.$name.'" value="'.$idx.'" '.$selected.'>';
      echo '<label class="btn btn-outline-primary" for="'.$name.'">'.$db["name"].'</label>';
      $idx++;
}
      echo '<input type="radio" class="btn-check" name="uploadidxradio" id="uploadidxnew" value="new">';
      echo '<label class="btn btn-outline-success" role="button" for="uploadidxnew" data-bs-toggle="collapse" data-bs-target="#newidxname" aria-expanded="false" aria-controls="newidxname">NEW</label>';
?>
        </div>
      </div> <!-- input group -->
      <div class="input-group mb-3 pt-1 collapse" id="newidxname">
        <input type="text" name="newidxname" class="form-control" placeholder="Name of new index" aria-label="Name of new Index" />
        <div class="input-group pt-2">
          <label for="btn-group-new-idx-type">Index type</label>
          <div class="btn-group ps-2" id="btn-group-new-idx-type" role="group" aria-label="Index type">
            <input type="radio" class="btn-check" name="uploadidxtyperadio" id="idxtype_emb" value="emb" checked>
            <label class="btn btn-outline-info" for="idxtype_emb">Embeddings</label>
            <input type="radio" class="btn-check" name="uploadidxtyperadio" id="idxtype_tok" value="tok">
            <label class="btn btn-outline-info" for="idxtype_tok">Tokens</label>
            <!-- input type="radio" class="btn-check" name="uploadidxtyperadio" id="idxtype_hyb" value="hyb">
            <label class="btn btn-outline-info" for="idxtype_hyb">Hybrid</label -->
          </div>
        </div>
      </div> <!-- input group -->
    </form>
  </div> <!-- tab -->
<?php
   if(isset($_GET["files"])) {
       echo '<div class="tab-pane fade active show" id="files" role="tabpanel">';
   }else{
       echo '<div class="tab-pane fade" id="files" role="tabpanel">';
   }
?>                            
    <label for="btn-group-files-idx">Index</label>
    <div class="input-group mb-3 pt-1">
      <div class="btn-group" id="btn-group-files-idx" role="group" aria-label="Index">
<?php
  $idx=0;
  $files_db = $config["default"];
  $files_section = "";
  if(isset($_GET["files"])) {
      [ $files_db, $files_section ] = \explode(":", $_GET["files"]);
  }
  foreach($config["databases"] as $db) {
      $name = 'filesidx'.$idx;
      $selected = $idx==$files_db?"checked":"";
      echo '<input type="radio" class="btn-check" name="filesidxradio" id="'.$name.'" value="'.$idx.'" '.$selected.'>';
      echo '<label class="btn btn-outline-primary" for="'.$name.'" role="button" data-bs-toggle="collapse" data-bs-target="#filesview'.$idx.'" aria-expanded="false" aria-controls="filesviewidx">'.$db["name"].'</label>';
      $idx++;
}
?>
      </div> <!-- btn-group -->
    </div> <!-- input-group -->
<?php
  $idx = 0;
  foreach($config["databases"] as $db) {
      $selected = $idx==$files_db?"checked":"";
      $collapsed = $selected ? "" : " collapse ";

      echo '<div class="container$collapsed" idx="filesview$idx">';
      if($selected && $files_section !== "") {
          // read dir
          $basedir = __DIR__."/../data/".$config["databases"][$idx]["file"]."/";
          if($files_section === "/main") {
              echo '<h5>Section: Default</h5>';
          }else{
              echo '<h5>Section: '.$files_section.'</h5>';
              $basedir = "$basedir$files_section/";
          }
          $dir = \opendir($basedir);
          echo '<a class="text-info" href="index.php?files='.$idx.':">Back</a><br/>';
      
          echo '<ul>';
          while(false !== ( $fname = readdir($dir)) ) {
              if (! is_dir($basedir.$fname)) {
                  echo '<li><a class="text-info" href="index.php?files='.$idx.':'.$files_section.'&download='.urlencode($fname).'">'.$fname.'</a>';
                  echo ' <a class="text-danger" href="index.php?files='.$idx.':'.$files_section.'&delete='.$fname.'">[delete]</a></li>';
              }
          }
          echo '</ul>';
          closedir($dir);
      }else{
          echo '<h5>Sections</h5>';
          echo '<ul>';
          echo '<li><a class="text-info" href="index.php?files='.$idx.':/main">Default</a></li>';
          foreach($db["sections"] as $section) {
              echo '<li><a class="text-info" href="index.php?files='.$idx.':'.$section.'">'.$section.'</a></li>';
          }
          echo '</ul>';
          echo '</div>';
      }
      $idx++;   
  }
?>

  </div>
</div>
</div>      
    </main>

    <footer class="position-fixed bottom-0 end-0 p-3">
        <a href="#" class="text-decoration-none text-dark me-3">
            <i class="bi bi-gear"></i>
        </a>
        <a href="#" class="text-decoration-none text-dark">
            <i class="bi bi-question-circle"></i>
        </a>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.min.js"></script>
</body>
</html>
