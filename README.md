systemctl status apache2     
sudo git clone https://github.com/rymonindra/my-social.git . /var/www/html/my-social

cd /var/www/html/
sudo git clone https://github.com/rymonindra/my-social.git .


mysql -u root -p     
sudo chown -R www-data:www-data /var/www/html/



<bold>
CREATE USER 'mintu'@'localhost' IDENTIFIED BY 'mintu123';

CREATE DATABASE social_media;

USE social_media;

USE social_media;

-- আগের users টেবিলটি মুছে নতুন করে তৈরি করা (নিরাপত্তার জন্য)
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);







GRANT ALL PRIVILEGES ON sqli.* TO 'rohan'@'localhost';
FLUSH PRIVILEGES;
