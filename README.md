

# AWS LEMP File Upload System with S3, RDS & CloudFront

This project demonstrates a full-stack file upload system hosted on an EC2 instance with a LEMP (Linux, Nginx, MySQL, PHP) stack, integrated with Amazon S3, RDS, and CloudFront for storage, database, and fast content delivery.


## 🚀 Features

- Upload images from a frontend HTML form
- Store images in an Amazon S3 bucket (ACL enabled)
- Retrieve and display uploaded images in a gallery
- View via both S3 and CloudFront URLs
- Save metadata (name, S3 URL, CF URL) in RDS (MySQL)
- Secure, scalable, and beginner-friendly

## 🧰 Tech Stack

- **Frontend**: HTML + CSS
- **Backend**: PHP (LEMP stack on EC2)
- **Database**: Amazon RDS (MySQL)
- **Storage**: Amazon S3 (ACL enabled)
- **CDN**: Amazon CloudFront
- **Web Server**: Nginx

## 📁 Project Structure

├── file.html # Upload form
├── upload.php # Handles upload logic to S3 + DB
├── gallery.php # Image gallery with CF links
├── delete.php # Delete from S3 + RDS
└── composer.json # AWS SDK dependency



## ⚙️ Setup Instructions

1. Launch EC2 (Amazon Linux 2), install LEMP stack:
   ```bash
   sudo yum install nginx php php-mysqlnd mariadb105-server
   sudo service nginx start
   sudo service php-fpm start
   
2. Create a folder for uploads:

mkdir /usr/share/nginx/html/uploads
chmod 777 uploads/

3. Set up Amazon S3 bucket with ACL + public access.

4. Create Amazon RDS (MySQL) DB & table:
5. 
CREATE DATABASE facebook;
USE facebook;
CREATE TABLE posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(20),
    s3url VARCHAR(200),
    cfurl VARCHAR(200)
);

5. Install AWS SDK using Composer:

curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer require aws/aws-sdk-php

6. Configure upload.php with:
Your bucket name
RDS endpoint + credentials
CloudFront URL logic
