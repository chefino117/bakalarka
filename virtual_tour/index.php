<!--
Copyright 2018 Simon Hrabos. All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.

2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE FREEBSD PROJECT "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
IN NO EVENT SHALL THE FREEBSD PROJECT OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR 
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT 
OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
-->
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Create Virtual Tour</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"> </script>
    <div id="full">
        <div id="pravy">
            <h1 id = "first">Already created virtual tours</h1>
            <?php 
            $myfile = fopen("virtual_tours.txt", "r");
            $counter = 0;
            $counter_minus = 0;
            while (($line = fgets($myfile)) !== false) {
                $modulo = $counter % 3;
                if ($modulo == 1){
                    echo "Date:";?>
                    <td><input type="text" value="<?php echo $line?>" name="<?php echo $counter?>" style="width: 100px" readonly/></td>
                    <?php
                }
                else if ($modulo == 2){
                    echo "Time:";?>
                    <td><input type="text" value="<?php echo $line?>" name="<?php echo $counter?>" style="width: 100px" readonly/></td>
                    <?php
                }
                else{
                    echo "Title:";?>
                    <td><input type="text" value="<?php echo $line?>" name="<?php echo $counter?>" style="width: 100px" readonly/></td>
                    <?php
                }
                if ($modulo == 2){?>
                    <form action="" method="post" enctype="multipart/form-data">
                        <input type="submit" name="<?php echo $counter?>" id="alreadyExists" value="run" />
                        <input type="submit" name="<?php echo $counter_minus?>" id="remove" value="remove" onclick="return comfirm('Are you sure want to delete');"/>
                    </form>
                            
            <?php
                }
                $counter++; 
                $counter_minus--;
            }
            fclose($myfile);
            ?>
        </div>

        <div id="lavy">
            <h1 id="second">Create new virtual tour</h1>
            <form id="lavy" action="" method="post" enctype="multipart/form-data">
            Insert Virtual Tour title:<br>
            <input type="text" name="tour_name">
            <br>
            <br>
            Select trajectory or video to upload:<br>
            <input type="file" name="fileToUpload" id="fileToUpload">
            <input type="submit" value="Upload File" name="submit" id="upload">
            <br>
            <br>
            <input type="submit" name="test" id="test" value="Start Virtual Tour" /><br/>
            <img src="img/loading.gif" id="loader" style="display:none"/ >
            </form>
        </div>
    </div>
</body>
</html>

<script>
$('#upload').click(function(){  //for loading image
  $('#loader').show();
});

$('#test').click(function(){    //for loading image
  $('#loader').show();
});

function nextPage() {
    window.open("tour.html", "_self");
}

function samePage() {
    window.open("index.php", "_self");
}
</script>

<?php
ob_start();

$myfile = fopen("virtual_tours.txt", "r");
$counter = 0;
while (($line = fgets($myfile)) !== false) {    //get number of already uploaded virtual tours
    $counter++;
}
fclose($myfile);

for ($i=0; $i < $counter; $i++) {   //controls all of the run buttons
    if(isset($_POST[$i])){
        $myfile = fopen("virtual_tours.txt", "r");
        $counter = 0;
        while (($line = fgets($myfile)) !== false) {
            if ($counter == $i-2){
                $result = $line;
            }
            $counter++;
        }
        fclose($myfile);
        $fw = fopen("last_tour.txt", "w");
        fwrite($fw, $result);
        fclose($fw);
        echo '<script type="text/javascript">',
             'nextPage();',
             '</script>';
    }
}

for ($i=0; $i < $counter; $i++) {   //controls all of the rremove buttons
    
    if(isset($_POST[-$i])){
        $fname = "virtual_tours.txt"; 
        $lines = file($fname);
        $f = fopen($fname, "w"); 
        $c = 0;
        $out = "";
        foreach($lines as $line){
            if ($c == abs($i) - 2){
                $line = str_replace(' ', '', $line);
                $line = str_replace('/n', '', $line);
                if (DIRECTORY_SEPARATOR == '/') {   //for linux shell command
                    $del = "rm -rf " . $line;
                    shell_exec($del);
                }

                if (DIRECTORY_SEPARATOR == '\\') {  //for windows shell command
                    $del = "rmdir /s /q " . $line;
                    shell_exec($del);
                }
            } 
            if ($c != abs($i) - 2 && $c != abs($i) - 1 && $c != abs($i)){
                $out .= $line; 
            }
            $c ++;
        }   
        fwrite($f, $out);
        fclose($f);  
        echo '<script type="text/javascript">', //reloads page with removed virtual tour
             'samePage();',
             '</script>';
    }
}

function txt_count()    //checks number of text files in uploads diretory
{
    $directory = "uploads/";
    $filetxt = 0;
    $filestxt = glob($directory . "*.{txt}",GLOB_BRACE);
    $txtcount = count($filestxt);
    if ($txtcount >= 1){
        return 1;
    }
    else {
        return 0;
    }
}

function mp4_count()    //checks number of video files in uploads diretory
{
    $directory = "uploads/";
    $filemp4 = 0;
    $filesmp4 = glob($directory . "*.{mp4,MP4}",GLOB_BRACE);
    $mp4count = count($filesmp4);
    if ($mp4count >= 1){
        return 1;
    }
    else {
        return 0;
    }
}


if(isset($_POST["submit"])) {   //when upload button pressed
    error_reporting();
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $file_name = basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
    $txt_count = txt_count();
    $mp4_count = mp4_count();
    if ($txt_count === 1 && $imageFileType === "txt"){  //when more than 1 trajectory uploaded
        $output = shell_exec('ls');
        echo "<pre>$output</pre>";
        echo '<p style="padding-left: 60px; font-size: 20px;">' . "Trajectory already uploaded. \n";
        $uploadOk = 0;
    }
    if ($mp4_count === 1 && $imageFileType === "mp4"){  //when more than 1 video uploaded
        echo '<p style="padding-left: 60px; font-size: 20px;">' . "Video already uploaded. \n";
        $uploadOk = 0;
    }

    if (file_exists($target_file)) {    // Check if file already exists
        echo '<p style="padding-left: 60px; font-size: 20px;">' . "File already exists. \n";
        $uploadOk = 0;
    }

    if($imageFileType != "txt" && $imageFileType != "mp4") {    // Allow only certain file formats (mp4, txt)
        echo '<p style="padding-left: 60px; font-size: 20px;">' . "Only TXT and MP4 files are allowed. \n";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
    echo '<p style="padding-left: 60px; font-size: 20px;">' . "Sorry, your file was not uploaded.";

    } else {    //everything is OK, we can upload file
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            echo '<p style="padding-left: 60px; font-size: 20px;">' . "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
        } else {
            echo '<p style="padding-left: 60px; font-size: 20px;">' . "Sorry, there was an error uploading your file.";
        }
    }
}

if(isset($_POST['test'])){  //we need to check new virtual tour title (if exists)
    $formvalue = $_POST['tour_name'];
    if (empty($formvalue)){
        echo '<p style="padding-left: 60px; font-size: 20px;">' . "Please insert Virtual Tour title.";
    }
    else{
        $with_line = $formvalue . "\n";
        $myfile = fopen("virtual_tours.txt", "r");
        $flag = false;
        while (($line = fgets($myfile)) !== false) {
            if (strcmp($line, $with_line) == 0){
                $flag = true;
            }
        }
        fclose($myfile);
        if ($flag){
            echo '<p style="padding-left: 60px; font-size: 20px;">' . "Virtual Tour with this title already exists";
        }
        else{
            run($formvalue);
        }
    }
}

function run($title)    //function, that is called after start virtual tour button pressed, we need to chcek if everything is ok, then create new directory, move files, save new virtual tour title...
{
    $directory = "uploads/";
    $filetxt = 0;
    $filemp4 = 0;
    $filestxt = glob($directory . "*.{txt}",GLOB_BRACE);
    $filesmp4 = glob($directory . "*.{mp4,MP4}",GLOB_BRACE);
    $txtcount = count($filestxt);
    $mp4count = count($filesmp4);
    if ($txtcount < 1){     //trajectory missing
        echo '<p style="padding-left: 60px; font-size: 20px;">' . "No trajectory uploaded";
    }
    elseif ($mp4count < 1) {    //video missing
        echo '<p style="padding-left: 60px; font-size: 20px;">' . "No video uploaded";
    }
    else {  //everything OK, we can create new directory for uplaoded files
        $cmd = "mkdir " . $title;
        shell_exec($cmd);
        $cmd = "uploads/" . $title . "_temp.txt" ;
        $final_trajectory = $title . "/" . $title . ".txt" ;
        $txt_file = glob("uploads/*.txt");
        $mp4_file = glob("uploads/*.mp4");
        $MP4_file = glob("uploads/*.MP4");
        rename($txt_file[0], $cmd);
        $pos = strpos($cmd, "/"); 
        $trajextory = substr($cmd, $pos);
        $trajextory = $title . $trajextory;
        rename($cmd, $trajextory);
        if (!empty($MP4_file)){
            $cmd = "uploads/" . $title . ".MP4" ;
            rename($MP4_file[0], $cmd);
        }
        if (!empty($mp4_file)){
            $cmd = "uploads/" . $title . ".mp4" ;
            rename($mp4_file[0], $cmd);
        }
        $pos = strpos($cmd, "/"); 
        $movie = substr($cmd, $pos);
        $movie = $title . $movie;
        rename($cmd, $movie);

        $cmd = "ffmpeg -i " . $movie . " -qscale:v 4 -vf fps=10 " . $title . "/thumb%04d.jpg -hide_banner"; //we need to make photos from video
        echo shell_exec($cmd);

        $command = "ffmpeg -i " . $movie;
        $command = $command . " 2>&1";
        exec($command, $output);
        foreach ($output as $line) {    //getting video length information
            $findme = "Duration";
            $pos = strpos($line, $findme);
            if ($pos) {
                $line = substr($line, 12, 11);
                $milis = substr($line, 9, 2);
                $seconds = substr($line, 6, 2);
                $minutes = substr($line, 3, 2);
                $hours = substr($line, 0, 2);
                $seconds = $seconds + ($minutes * 60) + ($hours * 3600);
                $seconds = $seconds * 10;
            }
        }
        $linecount = 0;
        $handle = fopen($trajextory, "r");
        while(!feof($handle)){
          $line = fgets($handle);
          $linecount++;
        }
        fclose($handle);

        $delete_lines = $linecount - $seconds + 15; //synchonization, we need to delete some photos and some points of trajectory
        if ($delete_lines > 0){
            $counter = 0;
            $handle = fopen($trajextory, "r");
            $handle2 = fopen($final_trajectory, "w");
            while(!feof($handle)){
                $line = fgets($handle);
                if ($counter > $delete_lines){
                    fwrite($handle2, $line);
                }
                $counter++;
            }
        }
        fclose($handle);
        fclose($handle2);
        $fw = fopen("last_tour.txt", "w");
        fwrite($fw, $title);
        fclose($fw);
        $date = date("Y.m.d") . "\n";
        $time = date("h:i:sa") . "\n";
        $myfile = fopen("virtual_tours.txt", "a");
        $with_line = $title . "\n";
        fwrite($myfile, $with_line);
        fwrite($myfile, $date);
        fwrite($myfile, $time);
        fclose($myfile);
        echo '<script type="text/javascript">',
             'nextPage();',
             '</script>';
        //header("Location: tour.html");  //new virtual tour is creatied, we can open it
    }
}

?>


