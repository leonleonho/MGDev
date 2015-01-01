<!DOCTYPE html>
<html>
    <head>
        <title>Edit Photos</title>
        <link rel="stylesheet" href="../css/bootstrap.css">
        <link rel="stylesheet" href="../css/style.css">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
        <script src="../js/isotope.js"></script>
        <script src="js/mainAdmin.js"></script>
        
        
        
        
        
        <script>
            /*
            Initalizing handlers 
            */
            $( document ).ready(function() {
                $("#deleteSubmit").click(function(){choice = "DELETE";});
                $("#moveSubmit").click(function(){choice = "MOVE";});
                
                $('#editForm').submit(function() {
                    confirm(choice + " all selected photos?");
                });
                
                $("#editOptions").click(function(event){
                    if($(event.target).is("#confirmSelectionBtn")) {
                                                
                        // store selected photos in hidden field
                        $('#selectedPhotosHidden').val(selectedPhotos);
                        if (selecting){
                            $("#confirmSelectionBtn").text("Unconfirm");
                            selecting = false;
                        } else {
                            $("#confirmSelectionBtn").text("Confirm Selection");
                            selecting = true;
                        } 
                    } 
                });
            });
            var choice = "";
            var selectedPhotos = [];
            var selecting = true;
        </script>
        
        
        
        
        
        <link href='http://fonts.googleapis.com/css?family=Open+Sans:300italic' rel='stylesheet' type='text/css'>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php
/**
    Calling the corresponding JS functino to build the page. 
  */
$albumID = $_GET['albumID'];
$albumName = $_GET['albumName'];
if(!isset($_GET['pageNumber'])) {
    $pageNumber = 0;
} else {
    $pageNumber = $_GET['pageNumber'];
}

echo "<script>$(document).ready(function() {
    loadPictures($albumID, '$albumName', '$pageNumber');
  });</script>";
        ?>
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
                        <img class = "hidden-xs" src = "../images/style/logo.png" alt = "logo"/>
                        <img class = "visible-xs" src = "../images/style/logo.png" alt = "logo" width = "auto" height = "50"/>

                    </a>
                </div>
                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="bs-navbar-collapse-1">
                    <ul class="nav navbar-nav navbar-right">
                        <li><a id = "gallery" href = "viewCategories.php">Gallery</a></li>
                        <li><a id = "blog" href = "#">Blog</a></li>
                        <li><a id = "booking" href = "#">Booking</a></li>
                    </ul>
                </div>
            </div>
        </nav>    
        
        
        
        
        
        
        <!-- Edit Photos Options -->
        <form id="editForm" action="php/edit_photos.php" method="post" enctype= "multipart/form-data">
            <div id = "editOptions">
                <button type="button" id="confirmSelectionBtn">Confirm Selection</button>
                <input type='submit' id="deleteSubmit" value='Delete' name='delete'>
        <input type='submit' id="moveSubmit" value='Move' name='move'>

            </div>
            <label>Selected photos<br>
                <input type="text" name="selected_photos" id="selectedPhotosHidden">
            </label>
        </form>
        <!-- End Edit Photo Options -->
        
        
        
        
        
        <div class = "content container-fluid" id = "content">

        </div>
        <div id = "bottomRight">
            <address>
                <strong>Mike Gonzales</strong><br />
                (604) 111-1111<br />
                <a href = "mailto:#">Mikegonzales@mail.com</a><br />
            </address>
        </div>

    </body>

</html>

