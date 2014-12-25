<?php
require '../../php/connection.php';
$createdNewDir = 0;
$ds          = "/";  //1

$storeFolder = ".." .$ds . ".." .$ds . 'images' . $ds . "photos";   //2

if (!empty($_FILES)) {

    $tempFile = $_FILES['file']['tmp_name'];          //3             

    $targetPath = dirname( __FILE__ ) . $ds. $storeFolder . $ds;  //4

    $randName = uniqid('', true) . "." . pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    
    $targetFile =  $targetPath. $randName;  //5
    
    move_uploaded_file($tempFile,$targetFile); //6
    
    echo 'images' . $ds . "photos" . $ds .$randName;
    //echo $storeFolder . $ds . $randName;
} else {
    //Output the actual html page if needed. 
    ?>
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
    <html lang="en">
    <head>
        <link rel="stylesheet" href="../../css/bootstrap.css">
        <link rel="stylesheet" href="../../css/style.css">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic' rel='stylesheet' type='text/css'>
        <script src="../../js/main.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
        <title>Proccessing Photos</title>
    </head>
    <body>
   <nav class="navbar navbar-default">
      <div class="container">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-navbar-collapse-1">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            </button>
        <a class="navbar-brand" href="#">
            <img class = "hidden-xs" src = "../../images/style/logo.png" alt = "logo"/>
            <img class = "visible-xs" src = "../../images/style/logo.png" alt = "logo" width = "auto" height = "50"/>

        </a>
        </div>
            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-navbar-collapse-1">
              <ul class="nav navbar-nav navbar-right">
                <li><a href = "#">Proccessing Images</a></li>
            </ul>
        </div>
    </div>
    </nav>
    <!-- Progress bar holder -->
    <div class = "container">
         <div class="panel panel-default" >
          <div class="panel-body">
            <progress id = "progress" value="0">
            </progress>
            <p align = "center" id = "percentDone"></p>
            </div>
        </div>

         <div class="panel panel-default" style ="height: 200px; overflow: auto;">
          <div class="panel-body">
                <h1>Warnings</h1>
                 <div id = "warnings"></div>
            </div>
        </div>
         <div class="panel panel-default" style ="height: 400px; overflow: auto;">
          <div class="panel-body">
                <h1>Status</h1>
                <div id="information" style="width"></div>
            </div>
        </div>

        
        
    </div>

    </body>
    </html>
    <?php
    //Flush the output stream
    echo str_repeat(' ',1024*64);
    //Fix json , decode and then proccess the images.
    $json = str_replace("\\r\\n", '', $_POST["jsonText"]);
    $json = json_decode($json);
    $albumName = $_POST["albumName"];
    if($_POST["albumNameDropDown"] != "null") {
        $albumName = $_POST["albumNameDropDown"];
    }
    processImages($json, $albumName);
}

error_reporting(0); //Turn off error reporting because we already deal with it manually. 
/**
    Pass in an array of images and this will create the thumbnails and 
    add the exif data into the database!
    It will return false if one of the files fail to make a thumb; however it will 
    try to continue to read the rest of the array. If it fails it will not added to the database. 
    It will not delete the failed images. 

    It will not rip EXIF meta data from non jpeg files because they don't have any.
    EX:
        processImages($arrayOfImages);
    RETURN VALUES:
        true : Everything ran smoothly
        Array: Returns array of the file names of the failed images. (Could be used to delete them);
*/
function processImages($arrayOfImages, $albumName) {
    $return = true;
    $failedFiles = array();
    $count = 0;
    $sizeOfArray =  sizeof($arrayOfImages);
    $query = "INSERT INTO photo (photoDate, dateTaken, aperture, ISO, focalLength, camera, location) VALUES";
    $last;
    $first = null;
    //Connect to the database!
    global $conn;
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    //Make thumbnails for each photo, then rip the EXIF data and insert it into the database
    foreach($arrayOfImages as $image) {
        set_time_limit(60);
        $image = "../../".$image;
        if(makeThumb($image) !== true) {
            echo '<script>document.getElementById("warnings").innerHTML = "Failed to proccess'. $image . '<br />" 
                + document.getElementById("warnings").innerHTML</script>';
            $return = false;
            array_push($failedFiles, $image);
            $sizeOfArray--;
            continue;
        }
        /*
            Checking file type we will only rip data if it is a jpeg. 
            Only jpeg holds metadata :(
        */
        $fileType = strrpos($image, ".");
        $fileType = substr($image, $fileType);
        if($fileType == ".jpg" || $fileType == ".JPG") {
            $temp = readPhoto($image);
            /*
                Checking if the EXIF exists, if it doesn't we will not save
                the data.
            */
            if($temp["model"] == NULL) {
                $query = $query . sprintf("(CURDATE(), null, null, null, null, null, '%s'), " , $image);
            } else {
               $query = $query . sprintf("(CURDATE(), '%s', '%s', '%d', '%s', '%s', '%s'), " , $temp['date'], $temp['aperture'], $temp['iso'], $temp['focal'], $temp['model'], $image); 
            }
        } else {
            $query =  $query . sprintf("(CURDATE(), null, null, null, null, null, '%s'), " , $image);
        }
        //Update the last and first so we know what to put in which album.
        $last = $image;
        if($first == null) {
            $first = $image;
        }
        /*
            Update the progress bar
        */
        $percent = ++$count / $sizeOfArray;
        echo '<script language="javascript">
            document.getElementById("progress").value = '.$percent.';
            document.getElementById("information").innerHTML="'.$image.' processed.<br />" + document.getElementById("information").innerHTML;
            $("#percentDone").html("'.floor($percent * 100) .' %");
            </script>';
        echo str_repeat(' ',1024*64);
        flush();
    }
    $query = substr($query,0,strlen($query)-2); //We need to trim off the extra comma that we put in.
    /*
        Running the SQL query. 
    */
    if (!mysqli_query($conn, $query)) {
        echo "Error: " . $query . "<br>" . mysqli_error($conn);
    }
    putIntoAlbum($first, $last, $albumName);
    /*
        Say that we are done!
    */
    echo '<script language="javascript">document.getElementById("information").innerHTML = "Process completed<br />" 
        + document.getElementById("information").innerHTML</script>';
    mysqli_close($conn);
    if($return) {
        return $return;
    }
    return $failedFiles;
    
}
/**
    This functin adds the photos found from the start to end into the 
    photo album in the database.
    Parameters 
        $start = The directory to the first image uploaded
        $end   = The directory to the last image uploaded
        $albumName = The album to reference
    Return values:
        -1 = Sql failure
        true = everything good
*/
function putIntoAlbum($start, $end, $albumName) {
    global $conn;
    $id;
    $query = "SELECT albumID FROM album WHERE albumName = '$albumName'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) == 0) {
        $query = "INSERT INTO album (albumName, albumDate) VALUES ('$albumName', CURDATE())";
        $result = mysqli_query($conn, $query);
        $query = "SELECT albumID FROM album WHERE albumName = '$albumName'";
        $result = mysqli_query($conn, $query);
    } 
    while($row = mysqli_fetch_assoc($result)) {
        $id = $row["albumID"];
    }
    $query = "
    INSERT INTO photoalbum (photoID, albumID) 
    SELECT photoID, '$id'
    FROM photo
    WHERE photoID >= (SELECT photoID FROM photo WHERE location = '$start')
    AND photoID <= (SELECT photoID FROM photo WHERE location = '$end')";
    if (!mysqli_query($conn, $query)) {
            echo "Error: " . $query . "<br>" . mysqli_error($conn);
            return -1;
    } 
    return true;
}
/**
    To use pass in an image
    Remember to pass in the directory to the image.
    The images have to be either .jpg, .png, or .gif
    Change $dest if you want the final destination to be different
    Change $desired_height to fit the height you want. 
    RETURN VALUES :
        -1 = One of the files was not a png jpg or gif
        -2 = Failure to create image for some reason
        -3 = One of the files was not a valid jpg, png, or gif
        true = Everything is good
    example call:
    $arrayOfImages = array("../images/photos/0.jpg"
        ,"../images/photos/1.jpg"
        ,"../images/photos/2.jpg"
        ,"../images/photos/3.jpg");
    make_thumb($arrayOfImages);
*/
function makeThumb($src) {
    
    $dest = "../../images/thumbnails/"; //Change this if you want the destiation to be different. /
    $desired_height = 512;
    /* Get name of file  can set the destination inside of thumbnails*/
    $startAt = strrpos($src, "/");
    $finalDest = $dest . substr($src, ++$startAt);
    /*
    Getting the file type
    */
    $fileType = strrpos($src, ".");
    $fileType = substr($src, $fileType);
    if($fileType != ".jpg" && $fileType != ".png" && $fileType != ".gif" && $fileType != ".JPG" && $fileType != ".GIF" && $fileType != ".PNG") {
        return -1;
    }
    /* read the source image */
    if($fileType == ".jpg" || $fileType == ".JPG") {
        if(!($source_image = imagecreatefromjpeg($src))) {
            return -3;
        }
    } else if($fileType == ".png" || $fileType == ".PNG") {
        if(!($source_image = imagecreatefrompng($src))) {
            return -3;
        }
    } else if($fileType == ".gif" || $fileType == ".GIF") {
        if(!($source_image = imagecreatefromgif($src))) {
            return -3;
        }
    }
    $width = imagesx($source_image);
    $height = imagesy($source_image);

    /* find the "desired height" of this thumbnail, relative to the desired width  */
    $desired_width = floor($width * ($desired_height / $height));

    /* create a new, "virtual" image */
    $virtual_image = imagecreatetruecolor($desired_width, $desired_height);

    /* copy source image at a resized size */
    imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);

    /* create the physical thumbnail image to its destination */
    if($fileType == ".jpg" || $fileType == ".JPG") {
        if(!imagejpeg($virtual_image, $finalDest))
            return -2;
    } else if($fileType == ".png" || $fileType == ".PNG") {
        if(!imagepng($virtual_image, $finalDest)) {
            return -2;
        }
    } else if($fileType == ".gif" || $fileType == ".GIF") {
        if(!imagegif($virtual_image, $finalDest)) {
            return -2;
        }
    }
    return true;
}
/**
    Pass the soruce of a photo into the the function and it will return the important EXIF values back
    RETURN
        array: Returns an array of the the following:
            filename,
            iso,
            exposure
            focal length
            resolution
            date
            model of camera
*/
function readPhoto($src) {
    $exif = exif_read_data($src, "EXIF");
    $return = array( "name" =>  $exif['FileName']
                    ,"aperture" => $exif['COMPUTED']['ApertureFNumber']
                    ,"iso" => $exif['ISOSpeedRatings']
                    ,"exposure" => $exif['ExposureTime']
                    ,"focal" => $exif['FocalLength']
                    ,"resolution" => $exif['COMPUTED']['Width'] ." x " . $exif['COMPUTED']['Height']
                    ,"date" =>  $exif['DateTimeOriginal']
                    ,"model" => $exif['Model']
    );
    /*echo "<pre>";
        var_dump($exif);
        var_dump($return);
    echo "</pre";*/
    return $return;
}
?>

