<?php 
require './php/logged_in.php'; 
?>

<html>
    <body>
        <form action="php/change_password.php" method="post" enctype= "multipart/form-data">
            <div class = "form-group">
                <label>Current Password<br>
                    <input type="password" name="current_pass" id = "oldPass">
                </label>
            </div>
            <div class = "form-group">
                <label>New Password<br>
                    <input type="password" name="new_pass" id = "newPass">
                </label>
            </div>          
            <div class = "form-group">
                <label>Re-enter Password<br>
                    <input type="password" name="confirm_pass" id = "confirmPass">
                </label>
            </div>
            <button type="submit" id = "submit">Change Password</button>
        </form>
        
        <h1>Change Password</h1>
        <br>
        <a href="php/logout.php">Logout</a>
        <br>
        <a href="control_panel.php">Control Panel</a>
    </body>
</html>