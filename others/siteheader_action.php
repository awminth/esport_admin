<?php
include('../config.php');
include(root.'lib/vendor/autoload.php');

$action = $_POST['action'];

if($action == 'show'){       
    $out = '';
    $sql = "SELECT s.*,u.UserName FROM tblsiteheader s,tbluser u WHERE s.UserID=u.AID ";
    $result = $con->query($sql);
    if (!$result) {
        die("Query failed: " . $con->error);
    }
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $img_url = roothtml.'lib/images/'.$row['Img'];
            if($row["Img"] == ""){
                $img_url = roothtml.'lib/images/noimage.jpg';
            }
            $out .= '
            <div class="card">
                <a href="#" id="btnEdit" 
                    data-aid="'.$row['AID'].'"
                    data-title="'.$row['Title'].'"  
                    data-description="'.$row['Description'].'"
                    data-img="'.$row['Img'].'">
                    <img src="'.$img_url.'" alt="" class="card-img-top img-fluid"
                        style="height: 320px; width: 100%; object-fit: cover;">
                </a>
                <div class="card-body">
                    <a href="#" class="text-dark">
                        <h4 class="card-title ">'.$row['Title'].'</h4>
                    </a>
                    <p class="card-text text-muted">'.$row['Description'].'</p>
                    <span class="float-left font-small-2 text-info">
                        '.enDate1($row['DT']).', '.enTime1($row['DT']).'</span>
                </div>
            </div>
            ';
        }
        $con->close();
        echo $out;
    } else {
        $out.='
        <div class="container bg-secondary">
            <div class="p-5 text-center">
                <h5 class=" text-white">There is no record found.</h5>
            </div>
        </div>
        ';
        echo $out;
    } 
}

if($action == "save"){
    $userid = $_SESSION['esport_admin_userid'];
    $aid = $_POST["aid"];
    $title = $_POST["title"];
    $description = $_POST["description"];
    $oldimg = $_POST["oldimg"];
    $new_filename = "";
    $dt = date('Y-m-d H:i:s');
    if($_FILES['uploadimg']['name'] != ''){
        $filename = $_FILES['uploadimg']['name'];        
        $extension = pathinfo($filename,PATHINFO_EXTENSION);
        $file = $_FILES['uploadimg']['tmp_name'];
        $valid_extension = array("jpg","jpeg","png","JPG","JPEG","PNG");
        if(in_array($extension,$valid_extension)){
            $new_filename = date('Ymd-His').".". $extension;
            $new_path = root."lib/images/". $new_filename;
            if(move_uploaded_file($file,$new_path)){ 
                if($oldimg != "" && $oldimg != "noimage.jpg"){
                    unlink(root.'lib/images/'.$oldimg);
                } 
            }
        }
    }
    $sql = "UPDATE tblsiteheader SET Title = ?, Description = ?, Img = ?, DT = ?, UserID = ? WHERE AID = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ssssii", $title, $description, $new_filename, $dt, $userid, $aid);
    if($stmt->execute()){
        save_log($_SESSION['esport_admin_username']." Main Header အား ပြင်ဆင်သည်");
        mysqli_commit($con);
        echo 1;
    }else{
        mysqli_rollback($con);
        error_log("Error in siteheader_action.php: " . $stmt->error, 3, root.'others/my_error.log');
        echo 0;
    }
}



?>