<?php
$config = json_decode(\file_get_contents("../data/config.json"), true);
$endpoint = $config['endpoint'] ?? 'http://127.0.0.1:8091';
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
  <li class="breadcrumb-item "><a class=" text-info" href="index.php">Home</a></li>
  <li class="breadcrumb-item text-success active">Search</li>
</ol>
    </header>

<?php
require '../vendor/autoload.php';

$searchidx = $_POST["searchidxradio"];
$config["default"] = $searchidx;
\file_put_contents("../data/config.json", json_encode($config, JSON_PRETTY_PRINT));

$query = $_POST["query"];

$desc = [
    "class"=>$config["databases"][$searchidx]["class"],
    "location"=>__DIR__."/../data/".$config["databases"][$searchidx]["file"].".db"
];

$index = \Textualization\SemanticSearch\IndexFactory::make($desc);

function prepare_text(string $text) {
    $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5;
    
    $lines = explode("\n", $text);
    foreach($lines as &$line)
        $line = htmlentities($line, $flags, "UTF-8");
    $preview_lines = [ $lines[0] ];
    if(count($lines) > 1)
        $preview_lines[] = $lines[1];
    $preview = implode("<br/>", $preview_lines); # TODO, highlight search terms
    $full_text = implode("<br/>", $lines);
    return [$preview, $full_text];
}
$flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5;

?>
          
    <div class="container">
        <div class="row">
        <div class="col-12 text-primary py-3 mb-4">
          <h1>Results for: <?php echo htmlentities($query, $flags, "UTF-8"); ?></h1>
<?php                                          
$starttime = microtime(true);
$results = $index->search($query);
$endtime = microtime(true);
$timediff = $endtime - $starttime;

echo "<p><span class='text-secondary'>Search took ".$timediff."s</span></p>";
?>
<?php
if(!isset($_POST["search"])) {
    $running = json_decode(file_get_contents("$endpoint/health"), true);
    if($running['status'] != 'ok') {
        die("LLM still loading, try again in a minute.");
    }
    $client = new \GuzzleHttp\Client();
    $text = $index->fetch_document($results[0]->url, $results[0]->chunk_num)->text;
    $prompt = "<human>: $text\n$query\n<bot>:";
    $starttime = microtime(true);
    $response = $client->request('POST', "$endpoint/completion",
                                 [ 'json' => [
                                     "prompt"=> $prompt,
                                     "mirostat_tau"=>4,
                                     "n_predict"=>1024,
                                     "mirostat_eta"=>0.1,
                                     "temperature"=>0.2,
                                     "mirostat"=>2,
                                     "seed"=>1993
                                 ]
                                 ]);

    $json = $response->getBody();
    $json = json_decode($json, TRUE);
    $endtime = microtime(true);
    $timediff = $endtime - $starttime;
    $answer = $json["content"];
    $answer = str_replace('<|endoftext|>', '', $answer);

    $idx = 0;
    if(isset($_POST["index"])) {
        $idx = $_POST["index"];
    }

    [$preview, $full_text] = prepare_text($index->fetch_document($results[$idx]->url, $results[$idx]->chunk_num)->text);    
    echo "<p><span class='text-secondary'>Answer took ".$timediff."s</span></p>";
?>
        </div>
        </div>
        <div class="row">
            <div class="col-12">
               <div class="bg-success text-white fs-2 answer">
<?php
    echo "<p>" . htmlentities($answer, $flags, "UTF-8") . "</p>";
?>
                </div>
                <div class="passage">
                   <h3>Supporting passage:</h3>
                     <div class="passage-title text-secondary">Passage <?php echo ($idx+1).": ";
    echo '<a href="index.php?files='.$searchidx.':/main&download='.urlencode($results[$idx]->title).'">'.htmlentities($results[$idx]->title, $flags, "UTF-8").'</a>'; ?></div>
                    <div class="passage-preview"><?php echo $preview; ?>...</div>
                    <button type="button" class="btn btn-link read-more">Read more</button>
                    <div class="passage-text"><?php echo $full_text; ?></div>
                    <button type="button" class="btn btn-link read-more read-twin">Read less</button>
                </div>
            </div>
       </div>
        <div class="row">
            <div class="col-12">
                   <h3>Search results:</h3>
            </div>
<?php    
}else{
?>
        </div>
        </div>
        <div class="row">
<?php    
}

$idx = 0;
foreach($results as $result) {
    [$preview, $full_text] = prepare_text($index->fetch_document($result->url, $result->chunk_num)->text);
    ?>
            <div class="col-12">
                <div class="passage">
                    <form method="POST">
                    <div class="passage-title text-secondary">Passage <?php echo $idx+1;?>: <?php echo
    '<a href="index.php?files='.$searchidx.':/main&download='.urlencode($results[$idx]->title).'">'.htmlentities($result->title, $flags, "UTF-8").'</a>'; ?></div>
                    <div class="passage-preview"><?php echo $preview; ?>...</div>
                    <button type="button" class="btn btn-link read-more">Read more</button>
<?php
    echo '<input type="hidden" name="query" value="'.htmlentities($query, $flags, "UTF-8").'"></input>';
    echo '<input type="hidden" name="searchidxradio" value="'.$searchidx.'"></input>';
    echo '<input type="hidden" name="index" value="'.$idx.'"></input>';
?>
                    <input type="submit" name="answer" class="btn btn-sm btn-success" value="Answer from here"></input>
                    <div class="passage-text"><?php echo $full_text; ?></div>
                    <button type="button" class="btn btn-link read-more read-twin">Read less</button>
                    </form>
                </div>
            </div>
<?php        
        $idx++;
}
?>
        </div>
    </div>

    <footer class="position-fixed bottom-0 end-0 p-3">
        <a href="config.php" class="text-decoration-none text-dark me-3">
            <i class="bi bi-gear"></i>
        </a>
        <a href="help.php" class="text-decoration-none text-dark">
            <i class="bi bi-question-circle"></i>
        </a>
    </footer>

    <script src="assets/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.read-more').on('click', function () {
                $(this).siblings('.passage-preview').toggleClass('compressed');
                const passageText = $(this).siblings('.passage-text');
                passageText.toggleClass('expanded');
                const isExpanded = passageText.hasClass('expanded');
                const newText = isExpanded ? 'Read less' : 'Read more'
                if($(this).hasClass('read-twin')) {
                    $(this).siblings('.read-more').text(newText);
                    $(this).toggleClass('expanded');
                }else{
                    $(this).text(newText);
                    $(this).siblings('.read-more').toggleClass('expanded');
                }
            });
        });
    </script>
</body>
