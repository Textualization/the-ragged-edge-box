<?php

require '../vendor/autoload.php';

$index_class = "\\Textualization\\SemanticSearch\\VectorIndex";
$query = $_POST["query"];

$desc = [
    "class"=>$index_class,
    "location"=>__DIR__."/../data/vector.db"
];

$index = \Textualization\SemanticSearch\IndexFactory::make($desc);

echo "Query: ". $query ."<br/>";

$starttime = microtime(true);
$results = $index->search($query);
$endtime = microtime(true);
$timediff = $endtime - $starttime;

echo "Search took ".$timediff."s<br/>";

if(isset($_POST["search"])) {
    $idx = 0;
    foreach($results as $result) {
        echo "============================================================================================================\n";
        echo "$idx. $result<br>";
        echo $index->fetch_document($result->url, $result->chunk_num)->text."<br><br>";

        $idx++;
    }
}else{
     $client = new \GuzzleHttp\Client();
     $text = $index->fetch_document($results[0]->url, $results[0]->chunk_num)->text;
     $prompt = "<human>: $text\n$query\n<bot>:";
     $starttime = microtime(true);
     $response = $client->request('POST', 'http://localhost:8090/completion',
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
     echo "Answer took ".$timediff."s<br/>";
     echo $json["content"];
     echo "<br/>Supporting passage:<br/>";
     echo $results[0]."<br>";
     echo $text;
}
