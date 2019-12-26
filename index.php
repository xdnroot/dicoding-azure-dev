<?php
require_once "vendor/autoload.php";

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;

$storage_name = "visionpro";
$storage_key = "WG8N/lHjK7z5ykga6IAsKR7MCRRJbb6shgwmXotp5A8TrxGdonSapwCeIeItAXieCcNAAJwxdA8y5NxmqiOu3Q==";

$connection = "DefaultEndpointsProtocol=https;AccountName=$storage_name;AccountKey=$storage_key";
$container = "images";
$blobClient = BlobRestProxy::createBlobService($connection);

$display = "none";

  if (isset($_POST['upload'])) {
    $img = $_FILES['img'];
    if (strpos($img['type'], 'image') !== false) {
      if ($img['size'] <= 5242880) {
        $content = fopen($img['tmp_name'], "r");
        $filename = time()."-".str_replace(' ', '_', $img['name']);

        try {
            # Upload file as a block blob
            $content = fopen($img['tmp_name'], "r");
            $blobClient->createBlockBlob($container, $filename, $content);

            // List blobs.
            $listBlobsOptions = new ListBlobsOptions();
            $listBlobsOptions->setPrefix($filename);

            do{
                $result = $blobClient->listBlobs($container, $listBlobsOptions);
                foreach ($result->getBlobs() as $blob)
                {
                    global $img_name, $img_url;
                    $img_name = $blob->getName();
                    $img_url = $blob->getUrl();
                    $alert = '<p class="alert alert-success"><b>Success:</b> '.$img_name.' uploaded.';
                    $display = "flex";
                }

                $listBlobsOptions->setContinuationToken($result->getContinuationToken());
            } while($result->getContinuationToken());
            echo "<br />";

        }
        catch(ServiceException $e){
            $code = $e->getCode();
            $error_message = $e->getMessage();
            echo $code.": ".$error_message."<br />";
        }
      }else{
        $alert = '<span class="alert alert-danger"><b>Error:</b> Maximum file is 5MB.</span>';
      }
    }else{
      $alert = '<span class="alert alert-danger"><b>Error:</b> Please select image file only.</span>';
    }



    $sub_key = "Ocp-Apim-Subscription-Key: e46d816edcae425cb7c537b1eaedb31d";
    $api = "https://codingvision.cognitiveservices.azure.com/vision/v2.0/analyze";
    $_params = [
        "visualFeatures" => "Categories,Description,Color",
        "details" => "",
        "language" => "en",
    ];
    $get_params = [];
    foreach ($_params as $name => $value)
    {
      $get_params[] = $name.'='.urlencode($value);
    }
    $uri = $api."?".join('&', $get_params);
    //$img_url = "https://visionpro.blob.core.windows.net/images/1577372255-love-photos-wallpaper-5.jpg";
    $post_params = json_encode(["url" => $img_url]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $uri);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Content-Type: application/json",
      $sub_key
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
    $result = curl_exec($ch);
    $json = json_decode($result);
    curl_close($ch);

    $tags = "";
    foreach ($json->description->tags as $val) {
      $tags .= "#".$val." ";
    }
    $caption = $json->description->captions[0]->text;
    preg_match('/(.*)\/(.*)/', $img_url, $string);
    $name = $string[2]." (".$json->metadata->width."x".$json->metadata->height.")";
  }
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>visionPro</title>
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <script src="./js/jquery.js"></script>
    <script src="./js/bootstrap.min.js"></script>
    <script>
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
    </script>
  </head>
  <body>
    <div class="container">
      <h1 style="display:inline;margin-right:20px"><b>visionPro</b></h1>
      <h4 style="display:inline">Analyze image with Azure Compute Vision</h4>
      <br><br>
      <form action="" method="post" enctype="multipart/form-data">
        <div class="form-inline">
          <label for="file" class="mb-2 mr-sm-2">Select Images: </label>
          <input type="file" class="form-control mb-2 mr-sm-2" name="img" id="image" required>
          <button type="submit" class="btn btn-primary mb-2" name="upload">Upload</button>
        </div>
      </form>
      <br>
      <div id="alert"><?=@$alert;?></div>
      <!-- end form -->

      <br>

      <div class="row" id="analyze" style="display:<?=@$display;?>;">
        <div class="col-md-4 col-sm-12">
          <img src="<?=@$img_url;?>" style="width:100%">
          <br>
          <span style="color:gray;opacity:0.6;font-size:14px">Download: <a href="<?=@$img_url;?>" style="color:gray;"><?=@$name;?></a></span>
        </div>

        <div class="col-md-8 col-sm-12">
          <ul class="nav nav-pills" role="tablist">
            <li class="nav-item">
              <a class="nav-link active" data-toggle="pill" href="#summary">Summary</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" data-toggle="pill" href="#details">Details</a>
            </li>
          </ul>

          <?php
          ?>

          <!-- Tab panes -->
          <div class="tab-content">
            <div id="summary" class="container tab-pane active"><br>
              <b><?=@$caption;?></b>
              <br>
              <span style="color:blue;opacity:0.8"><?=@$tags;?></span>
            </div>
            <div id="details" class="container tab-pane fade"><br>
              <div class="form-group">
                <label for="comment">JSON Details:</label>
                <textarea class="form-control" rows="10" id="json" readonly><?=@$result;?></textarea>
              </div>
            </div>
          </div>
        </div>
      </div>





    </div>
  </body>
</html>
