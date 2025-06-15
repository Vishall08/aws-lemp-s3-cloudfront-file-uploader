<?php 
require 'vendor/autoload.php'; 

use Aws\S3\S3Client; 
use Aws\Exception\AwsException; 

// S3 client configuration
$s3Client = new S3Client([ 
    'version' => 'latest', 
    'region'  => 'ap-south-1', 
    'credentials' => [ 
        'key'    => 'YOUR_ACCESS_KEY', 
        'secret' => 'YOUR_SECRET_KEY' 
    ] 
]); 

if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    if (isset($_FILES["anyfile"]) && $_FILES["anyfile"]["error"] == 0) { 
        $allowed = [
            "jpg" => "image/jpg", 
            "jpeg" => "image/jpeg", 
            "gif" => "image/gif", 
            "png" => "image/png"
        ]; 

        $filename = $_FILES["anyfile"]["name"]; 
        $filetype = $_FILES["anyfile"]["type"]; 
        $filesize = $_FILES["anyfile"]["size"]; 

        $ext = pathinfo($filename, PATHINFO_EXTENSION); 
        if (!array_key_exists($ext, $allowed)) {
            die("❌ Invalid file format.");
        }

        if ($filesize > 10 * 1024 * 1024) {
            die("❌ File size exceeds 10MB.");
        }

        if (!in_array($filetype, $allowed)) {
            die("❌ File type mismatch.");
        }

        if (!move_uploaded_file($_FILES["anyfile"]["tmp_name"], "uploads/" . $filename)) {
            die("❌ Failed to upload file.");
        }

        // Upload to S3
        $bucket = 'your-s3-bucket-name';
        $file_Path = __DIR__ . '/uploads/' . $filename; 
        $key = basename($file_Path); 

        try { 
            $result = $s3Client->putObject([ 
                'Bucket' => $bucket, 
                'Key'    => $key, 
                'Body'   => fopen($file_Path, 'r'), 
                'ACL'    => 'public-read', 
            ]); 

            $urls3 = $result->get('ObjectURL'); 
            $cfurl = str_replace("https://$bucket.s3.ap-south-1.amazonaws.com", "https://your-cloudfront-domain.cloudfront.net", $urls3); 

            // RDS DB connection
            $servername = "your-rds-endpoint"; 
            $username = "your-db-username"; 
            $password = "your-db-password"; 
            $dbname = "facebook"; 

            $conn = new mysqli($servername, $username, $password, $dbname); 
            if ($conn->connect_error) {
                die("❌ DB Connection failed: " . $conn->connect_error);
            } 

            $name = $_POST["name"]; 
            $sql = "INSERT INTO posts (name, s3url, cfurl) VALUES ('$name', '$urls3', '$cfurl')"; 
            if ($conn->query($sql) !== TRUE) {
                die("❌ Error saving to database: " . $conn->error);
            } 

            $conn->close(); 

            // Success Page
            echo "
            <!DOCTYPE html>
            <html>
            <head>
                <title>Upload Success</title>
                <style>
                    body {
                        background: linear-gradient(to right, #f2f2f2, #d9e2f3);
                        font-family: Arial, sans-serif;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        height: 100vh;
                    }
                    .success-box {
                        background: #ffffff;
                        padding: 30px;
                        border-radius: 12px;
                        box-shadow: 0 0 15px rgba(0,0,0,0.2);
                        width: 500px;
                        text-align: center;
                    }
                    .success-box h2 {
                        color: green;
                        margin-bottom: 20px;
                    }
                    .success-box a {
                        display: block;
                        margin: 10px 0;
                        color: #004080;
                        font-weight: bold;
                        text-decoration: none;
                        word-break: break-word;
                    }
                    .success-box a:hover {
                        text-decoration: underline;
                    }
                    .btn {
                        margin-top: 20px;
                        display: inline-block;
                        background-color: #004080;
                        color: #ffffff;
                        padding: 12px 22px;
                        font-size: 16px;
                        font-weight: bold;
                        border-radius: 8px;
                        text-decoration: none;
                        transition: background-color 0.3s ease;
                    }
                    .btn:hover {
                        background-color: #00264d;
                    }
                </style>
            </head>
            <body>
                <div class='success-box'>
                    <h2>✅ Image Uploaded Successfully</h2>
                    <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
                    <a href='$urls3' target='_blank'>🔗 View on S3</a>
                    <a href='$cfurl' target='_blank'>🔗 View via CloudFront</a>
                    <a class='btn' href='file.html'>← Upload Another</a>
                    <a class='btn' href='gallery.php'>📸 View Gallery</a>
                </div>
            </body>
            </html>";
            
        } catch (AwsException $e) { 
            echo "❌ S3 Upload Error: " . $e->getMessage(); 
        } 
    } else { 
        echo "❌ File upload error."; 
    } 
} 
?>
