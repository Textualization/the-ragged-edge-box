<?php
$config = json_decode(\file_get_contents("../data/config.json"), true);

function sluggify(string $str) : string {
    $result = \strtolower(\trim(\preg_replace('/[\s-]+/', "-", \preg_replace('/[^A-Za-z0-9-]+/', "-", \preg_replace('/[&]/', 'and', \preg_replace('/[\']/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $str))))), "-"));
    if ($result === "") {
        $result = "-";
    }
    return $result;
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
                               
    <style>
        .answer {
            padding-top: 2rem;
            padding-bottom: 1rem;
            padding-left: 2rem;
            padding-right: 2rem;
            margin-bottom: 1rem;
        }

        .passage {
            margin-bottom: 1rem;
        }

        .passage-title {
            font-weight: bold;
        }

        .passage-text {
            display: none;
        }

        .passage-preview {
            display: block;
        }

        .passage-preview.compressed {
            display: none;
        }

        .passage-text.expanded {
            display: block;
        }

        .read-more.read-twin {
            display: none;
        }
        .read-more.read-twin.expanded {
            display: block;
        }
    </style>
</head>
<body>
    <header class="bg-primary text-white text-center py-0">
        <h1 class="d-inline-block align-text-top"> <img src="logo.png" alt="Logo" width="100" height="100" class="d-inline-block align-text-middle">The RAGged Edge Box
</h1>
<ol class="breadcrumb py-0">
  <li class="breadcrumb-item "><a class="text-info" href="index.php">Home</a></li>
  <li class="breadcrumb-item text-success active">Upload</li>
</ol>
    </header>

<?php

require '../vendor/autoload.php';

if(!isset($_FILES['upload']["tmp_name"]) || $_FILES['upload']["tmp_name"]==""){
   echo("No file uploaded");
   exit;
}

$uploadidx = $_POST["uploadidxradio"];
$index_class = "\\Textualization\\SemanticSearch\\VectorIndex";
if($uploadidx == "new") {
    $uploadidx = count($config["databases"]);
    switch($_POST["uploadidxtyperadio"]){
        case "emb":
            $index_class = "\\Textualization\\SemanticSearch\\VectorIndex";
            break;
        case "tok":
            $index_class = "\\Textualization\\SemanticSearch\\KeywordIndex";
            break;
        case "hyb":
            $index_class = "\\Textualization\\SemanticSearch\\RerankedIndex";
            break;
    }
    $name = $_POST["newidxname"] ?? 'default';
    if($name === "") {
        $name = 'default';
    }
    $slug = sluggify($name);
    \mkdir("../data/$slug");
    $config["databases"][] = [
        "name"=> $name,
        "class"=> $index_class,
        "file"=> "$slug",
        "sections" => []
    ];
}
$config["default"] = $uploadidx;
\file_put_contents("../data/config.json", json_encode($config, JSON_PRETTY_PRINT));

$desc = [
    "class"=>$config["databases"][$uploadidx]["class"],
    "location"=>__DIR__."/../data/".$config["databases"][$uploadidx]["file"].".db"
];
//print_r($desc);

$magic = shell_exec("/usr/bin/file --brief --mime-type ".$_FILES['upload']["tmp_name"]);
$magic = trim($magic);

$text=null;
if($magic === "text/plain") {
    $text=file_get_contents($_FILES['upload']["tmp_name"]);
}
if($magic === "application/pdf") {
    $text=shell_exec("/usr/bin/pdftotext ".$_FILES['upload']["tmp_name"]." /dev/stdout");
}
if($magic === "application/msword") {
    $text=shell_exec("/usr/bin/antiword -t ".$_FILES['upload']["tmp_name"]);
}
if($magic === "text/html") {
    $text=shell_exec("/usr/bin/pandoc -f html -t plain ".$_FILES['upload']["tmp_name"]);
}
if($magic === "application/vnd.openxmlformats-officedocument.wordprocessingml.document"){
    $text=shell_exec("/usr/bin/pandoc -f docx -t plain ".$_FILES['upload']["tmp_name"]);
}
if($magic === "application/epub+zip"){
    $text=shell_exec("/usr/bin/pandoc -f epub -t plain ".$_FILES['upload']["tmp_name"]);
}
if($magic === "application/vnd.oasis.opendocument.text") {
    $text=shell_exec("/usr/bin/pandoc -f odt -t plain ".$_FILES['upload']["tmp_name"]);
}
if($text === null) {
    echo "Unknown file type: ".$magic;
}else{
    $starttime = microtime(true);

    $url = "file:///" . $_FILES['upload']["full_path"];
    $section = "/main";
    $dir = __DIR__."/../data/".$config["databases"][$uploadidx]["file"]."/";
    
    if($_POST["section"] !== "") {
        $section = sluggify($_POST["section"]);
        $url = "file:///" . $section . "/" . $_FILES['upload']["full_path"];
        $dir = "$dir$section/";
        if(! \file_exists($dir)) {
            \mkdir($dir);
            $config["databases"][$uploadidx]["sections"][] = $section;
            \file_put_contents("../data/config.json", json_encode($config, JSON_PRETTY_PRINT));
        }
    }

    $target = $dir.$_FILES['upload']["full_path"];
    if(\file_exists($target)) {
        echo '<p>File already exists. Specify a different section to upload the new file or delete the existing file from the chosen section.</p>';
    }else{
        \copy($_FILES['upload']["tmp_name"], $target);
    
        $inded = \Textualization\SemanticSearch\Ingester::ingest($desc, [], [
            "url"=>$url,
            "title" => $_FILES['upload']["name"],
            "text" => $text,
            "section" => $section,
            "license" => "confidential"
        ]);
        $endtime = microtime(true);
        $timediff = $endtime - $starttime;
    
        echo 'Uploaded in '.$timediff;
    }
}
?>

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
