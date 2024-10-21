<?php
$licenses = json_decode(\file_get_contents("../licenses.json"), true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The RAGged Edge Box</title>
    <link href="assets/bootstrap.min.css" rel="stylesheet">
    <style>                               
     @font-face {
         font-family: 'Cabin Sketch';
         font-style: normal;
         font-weight: 400;
         font-display: swap;
         src: url(assets/cabin_sketch.ttf) format('truetype');
     }
     @font-face {
         font-family: 'Neucha';
         font-style: normal;
         font-weight: 400;
         font-display: swap;
         src: url(assets/neucha.ttf) format('truetype');
     }
    </style>                                  
    <link href="assets/bootswatch.min.css" rel="stylesheet">
    <script src="assets/popper.min.js"></script>
    <script src="assets/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="assets/bootstrap-icons.css">
                               
</head>
<body>
    <header class="bg-primary text-white text-center py-0">
        <h1 class="d-inline-block align-text-top"> <img src="logo.png" alt="Logo" width="100" height="100" class="d-inline-block align-text-middle">The RAGged Edge Box
</h1>
<ol class="breadcrumb py-0">
  <li class="breadcrumb-item "><a class=" text-info" href="index.php">Home</a></li>
  <li class="breadcrumb-item text-success active">Help</li>
</ol>
    </header>

<?php
require '../vendor/autoload.php';
?>
    <div class="container">
      <div class="row">
        <div class="col-12 text-primary py-3 mb-4">
          <p>RAGged Edge Box, is an open-source Retrieval Augmented Generation (RAG) system designed to help you find and manage your confidential documents with ease. The state-of-the-art semantic search and AI-powered question-answering techniques make it simple to locate and access the information you need, all within the privacy of your own laptop.</p>
          <h2>How It Works</h2>
          <ul>
              <li>Main Page: Search, Upload, and Document browse.</li>
              <li>Search Results Page: Displays the results of your search queries and select passage to us to answer questions.</li>
          </ul>
          <h2>Open Source Licenses</h2>
          <p>RAGged Edge Box is (C) 2024 by <a href="https://textualization.com">Textualization Software Ltd.</a> available under the Apache Public License 2.0. It also includes the following software:</p>
          <ul>
<?php
  foreach($licenses["dependencies"] as $name => $blob) {
    echo '<li><a href="https://packagist.org/packages/' . $name . '\">' . $name . '</a> - '. implode(',', $blob["license"]).'</li>';
  }
?>
          </ul>
          <p>Plus the licenses for the programs and libraries listed <a href="box-licenses.txt">here</a>.</p>          
        </div>
      </div>
    </div>

    <footer class="position-fixed bottom-0 end-0 p-3">
        <a href="config.php" class="text-decoration-none text-dark me-3">
            <i class="bi bi-gear"></i>
        </a>
        <a href="#" class="text-decoration-none text-dark">
            <i class="bi bi-question-circle"></i>
        </a>
    </footer>
</body>
