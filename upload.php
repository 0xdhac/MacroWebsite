<?php

?>

<?php 
session_start();
if(!isset($_SESSION['admin']) || $_SESSION['admin'] == '0')
{
    header("Location: login.php");
    exit;
}

 if(isset($_POST['submit']))
 {
    $name       = $_FILES['fileToUpload']['name'];  
    $temp_name  = $_FILES['fileToUpload']['tmp_name'];  
    if(isset($name))
    {
        if(!empty($name))
        {      
            $location = 'uploads/';      
            if(move_uploaded_file($temp_name, $location.$name))
            {
                $version = GetFileVersion($location.$name);

                if($version === FALSE)
                {
                    echo "Failed to get file version.";
                    exit;                    
                }

                $sversion = "$version[1].$version[2].$version[3].$version[4]";

                include 'includes/dbh.inc.php';
                $md5     = mysqli_escape_string($mysqli, md5_file($location.$name));
                $query   = "INSERT INTO versions (version, md5) VALUES ('$sversion', '$md5');";
                $mysqli->query($query);

                echo 'File uploaded successfully<br>';
            }
        }       
    }  
    else 
    {
        echo 'What';
    }
}

function GetFileVersion($FileName) 
{
    $handle=fopen($FileName,'rb');
    if (!$handle) return FALSE;
    $Header=fread ($handle,64);
    if (substr($Header,0,2)!='MZ') return FALSE;
    $PEOffset=unpack("V",substr($Header,60,4));
    if ($PEOffset[1]<64) return FALSE;
    fseek($handle,$PEOffset[1],SEEK_SET);
    $Header=fread ($handle,24);
    if (substr($Header,0,2)!='PE') return FALSE;
    $Machine=unpack("v",substr($Header,4,2));
    if ($Machine[1]!=332) return FALSE;
    $NoSections=unpack("v",substr($Header,6,2));
    $OptHdrSize=unpack("v",substr($Header,20,2));
    fseek($handle,$OptHdrSize[1],SEEK_CUR);
    $ResFound=FALSE;
    for ($x=0;$x<$NoSections[1];$x++) {      //$x fixed here
        $SecHdr=fread($handle,40);
        if (substr($SecHdr,0,5)=='.rsrc') {         //resource section
            $ResFound=TRUE;
            break;
        }
    }
    if (!$ResFound) return FALSE;
    $InfoVirt=unpack("V",substr($SecHdr,12,4));
    $InfoSize=unpack("V",substr($SecHdr,16,4));
    $InfoOff=unpack("V",substr($SecHdr,20,4));
    fseek($handle,$InfoOff[1],SEEK_SET);
    $Info=fread($handle,$InfoSize[1]);
    $NumDirs=unpack("v",substr($Info,14,2));
    $InfoFound=FALSE;
    for ($x=0;$x<$NumDirs[1];$x++) {
        $Type=unpack("V",substr($Info,($x*8)+16,4));
        if($Type[1]==16) {             //FILEINFO resource
            $InfoFound=TRUE;
            $SubOff=unpack("V",substr($Info,($x*8)+20,4));
            break;
        }
    }
    if (!$InfoFound) return FALSE;
    $SubOff[1]&=0x7fffffff;
    $InfoOff=unpack("V",substr($Info,$SubOff[1]+20,4)); //offset of first FILEINFO
    $InfoOff[1]&=0x7fffffff;
    $InfoOff=unpack("V",substr($Info,$InfoOff[1]+20,4));    //offset to data
    $DataOff=unpack("V",substr($Info,$InfoOff[1],4));
    $DataSize=unpack("V",substr($Info,$InfoOff[1]+4,4));
    $CodePage=unpack("V",substr($Info,$InfoOff[1]+8,4));
    $DataOff[1]-=$InfoVirt[1];
    $Version=unpack("v4",substr($Info,$DataOff[1]+48,8));
    $x=$Version[2];
    $Version[2]=$Version[1];
    $Version[1]=$x;
    $x=$Version[4];
    $Version[4]=$Version[3];
    $Version[3]=$x;
    return $Version;
}
?>

<!DOCTYPE html>
<html>
<body>

<form method="POST" enctype="multipart/form-data">
    Select file:
    <input type="file" name="fileToUpload" id="fileToUpload"><br>
    <input type="submit" value="Upload" name="submit">
    <br>
</form>

</body>
</html>


<html>
<head>
    <title>Upload New Version</title>
</head>
<body>

<a href="control.php">Control Panel</a>

</body>
</html>