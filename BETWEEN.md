Between Sun & Moon Photobooth

Photobooth system for Between Sun & Moon Coffee Roasters

This project runs on a Raspberry Pi with a Canon DSLR camera and exposes the interface online via Cloudflare Tunnel.

Customers can take photos, print them, and optionally access their photos online.

System Overview

Customer
↓
Touch Screen (Raspberry Pi)
↓
Photobooth App
↓
Canon DSLR (gphoto2)
↓
Image Processing
↓
Local Storage
↓
Cloudflare Tunnel
↓
photo.betweensunandmoon.coffee

Hardware Requirements

Required hardware

Raspberry Pi 4 or Raspberry Pi 5

8GB RAM recommended

Canon DSLR (650D / 700D / 720D etc.)

USB camera cable

Touch screen monitor

Optional hardware

Photo printer

Ring light

Button trigger

External SSD

Recommended setup

Raspberry Pi 5

SSD storage

HDMI touchscreen

Software Stack

Operating System
Raspberry Pi OS

Camera Control
gphoto2

Photobooth Software
PhotoboothProject

Tunnel
Cloudflare Tunnel

Web Server
Apache + PHP

Install Raspberry Pi OS

Flash Raspberry Pi OS using Raspberry Pi Imager

https://www.raspberrypi.com/software/

Enable SSH during installation.

Install Dependencies

Run

sudo apt update
sudo apt upgrade -y

Install required packages

sudo apt install -y
git
php
php-curl
php-gd
php-cli
gphoto2
apache2
libapache2-mod-php
unzip

Install Photobooth

Go to home directory

cd /home/pi

Clone photobooth project

git clone https://github.com/PhotoboothProject/photobooth.git

Enter project folder

cd photobooth

Run installer

bash install-photobooth.sh

After installation photobooth UI will be available at

http://localhost

Camera Test

Connect DSLR then test

gphoto2 --auto-detect

Expected result

Canon EOS 650D

Test capture

gphoto2 --capture-image-and-download

Layout Configuration

Edit configuration file

photobooth/config/config.inc.php

Example collage layout

'collageLayout' => '2x3'

Install Cloudflare Tunnel

Install cloudflared

sudo mkdir -p --mode=0755 /usr/share/keyrings

curl -fsSL https://pkg.cloudflare.com/cloudflare-main.gpg
 | sudo tee /usr/share/keyrings/cloudflare-main.gpg >/dev/null

echo 'deb [signed-by=/usr/share/keyrings/cloudflare-main.gpg] https://pkg.cloudflare.com/cloudflared
 bookworm main' | sudo tee /etc/apt/sources.list.d/cloudflared.list

sudo apt update

sudo apt install cloudflared

Login Cloudflare

Run

cloudflared tunnel login

This will open the browser to authorize your domain.

Create Tunnel

Run

cloudflared tunnel create photo_btsm

Cloudflare Tunnel Config

Create file

~/.cloudflared/config.yml

Example configuration

tunnel: photo_btsm

credentials-file: /home/pi/.cloudflared/TUNNEL_ID.json

ingress:

hostname: photo.betweensunandmoon.coffee
service: http://localhost

service: http_status:404

Add DNS Route

Run

cloudflared tunnel route dns photo_btsm photo.betweensunandmoon.coffee

Run Tunnel

Run

cloudflared tunnel run photo_btsm

Photobooth will now be accessible at

https://photo.betweensunandmoon.coffee

Auto Start Tunnel

To install as system service

sudo cloudflared service install

Image Storage

Captured images stored in

/home/pi/photobooth/data/images

Backup Strategy

Recommended backup

External SSD or NAS

Example rsync backup

rsync -av /home/pi/photobooth/data/images /mnt/backup/

Security

Recommended

restrict admin panel

enable Cloudflare Access

disable directory listing

Future Features

Possible improvements

QR code download
Instagram sharing
AI background replacement
Customer gallery
Automatic event album
Face detection countdown
